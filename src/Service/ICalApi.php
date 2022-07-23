<?php

namespace App\Service;

use App\Entity\Ical;
use App\Entity\ReservationStatus;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Sabre\VObject;

require_once(__DIR__ . '/../app/application.php');

class ICalApi
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        if (session_id() === '') {
            $logger->info("Session id is empty");
            session_start();
        }
    }

    function importIcalForRoom($roomId): array
    {
        //get all ical Urls for room
        $urls = $this->getRoomIcalUrls($roomId);

        $responseArray = array();
        foreach ($urls as $url) {
            //read events

            $events = VObject\Reader::read(
                file_get_contents($url->getLink())
            );

            try {
                $this->logger->info("events found  " . count($events));
                $i = 0;
                $origin = "";
                foreach ($events->VEVENT as $event) {
                    $i++;
                    $this->logger->info("event number $i");

                    $today = date("d.m.Y");
                    $yesterday = new DateTime($today);
                    $interval = new DateInterval('P1D');
                    $yesterday->sub($interval);

                    $date_event = new DateTime($event->DTSTART);
                    $checkInDate = date('Y-m-d', strtotime($event->DTSTART));//user friendly date

                    if ($yesterday > $date_event) {
                        $this->logger->info($date_event->format('Y-m-d H:i:s') . " event in the past ");
                        continue;
                    }

                    $this->logger->info($date_event->format('Y-m-d H:i:s') . " event In future");
                    $this->logger->info("url is " . $url->getLink());
                    $checkOutDate = date('Y-m-d', strtotime($event->DTEND));//user friendly date
                    $summary = $event->SUMMARY;
                    $description = $event->DESCRIPTION;
                    $guestPhoneNumber = "";
                    $uid = $event->UID;
                    $email = "";
                    $origin = "";
                    $guestName = "";
                    $originUrl = "";

                    //Guesty
                    if (str_contains($url->getLink(), 'guestyforhosts.com')) {
                        $origin = "guestyforhosts.com";
                        $originUrl = $origin;
                        $this->logger->info("Link is from guesty " . $url->getLink());
                        $this->logger->info("Summary: " . $summary);
                        $pieces = explode("-", $summary);
                        $guestName = $pieces[1];
                        $this->logger->info("guest name is  " . $guestName);
                        $this->logger->info("Description: " . $description);
                        $pieces = explode(":", $description);
                        $guestPhoneNumber = $pieces[2];
                        $this->logger->info("guest phone is  " . $guestPhoneNumber);

                        $pos = strpos($guestPhoneNumber, "Email");
                        if ($pos > -1) {
                            $guestPhoneNumber = trim(substr($guestPhoneNumber, 0, $pos));

                            $email = trim(str_replace("ATTENDEE", "", $pieces[3]));
                            $this->logger->info("guest email is  " . $email);
                        }
                    } else if (str_contains($url->getLink(), 'airbnb')) {
                        if (str_contains($summary, "Not available")) {
                            $this->logger->info("Summary is not available for uid " . $uid);
                            continue;
                        }
                        $detailsPosition = strpos($description, 'details/');
                        $this->logger->info("detailsPosition is  " . $detailsPosition);
                        $temp = substr($description, $detailsPosition + strlen('details/'));
                        $this->logger->info("temp is  " . $temp);
                        $endOfConfirmationPosition = strpos($temp, 'Phone');
                        $this->logger->info("endOfConfirmationPosition is  " . $endOfConfirmationPosition);
                        $originUrl = trim(substr($temp, 0, $endOfConfirmationPosition));
                        $this->logger->info("confirmation code is  " . $originUrl);
                        $origin = "airbnb.com";
                        $guestName = "Airbnb Guest";
                    }
                    //booking.com

                    $this->logger->info($uid . " - " . $guestPhoneNumber);
                    //check if booking already imported
                    $reservationApi = new ReservationApi($this->em, $this->logger);
                    $reservation = $reservationApi->getReservationByUID($uid);

                    //if booking not imported
                    if ($reservation === null) {
                        $this->logger->info("booking has not been imported");

                        //create reservation
                        $response = $reservationApi->createReservation($roomId, $guestName, $guestPhoneNumber, $email, $checkInDate, $checkOutDate, $uid, true, $origin, $originUrl);
                        if ($response[0]['result_code'] != 0) {
                            $responseArray[] = array(
                                'result_code' => 0,
                                'result_message' => $response[0]['result_message'] . $uid
                            );
                        } else {
                            $responseArray[] = array(
                                'result_code' => 0,
                                'result_message' => 'Successfully imported reservation ' . $uid
                            );
                        }
                        $this->logger->info(print_r($responseArray, true));
                    } else {
                        $reservation->setCheckIn(new DateTime($checkInDate));
                        $reservation->setCheckOut(new DateTime($checkOutDate));
                        $response = $reservationApi->updateReservation($reservation);
                        if ($response[0]['result_code'] != 0) {
                            $responseArray[] = array(
                                'result_code' => 0,
                                'result_message' => $response[0]['result_message'] . $uid
                            );
                        } else {
                            $responseArray[] = array(
                                'result_code' => 0,
                                'result_message' => 'Successfully updated reservation ' . $uid
                            );
                        }
                    }
                    $this->logger->info(print_r($responseArray, true));
                }

                $this->cancelReservationsNotInIcal($events, $roomId, $origin);
            } catch (Exception $ex) {
                $this->logger->info($ex->getTraceAsString());
            }


        }
        return $responseArray;
    }

    function cancelReservationsNotInIcal($events, $roomId, $origin): void
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        try {
            // get all future reservations for origin
            $reservationApi = new ReservationApi($this->em, $this->logger);
            $reservations = $reservationApi->getReservationsByRoomAndOrigin($roomId, $origin);
            if ($reservations === null) {
                $this->logger->info("reservations for room $roomId and origin $origin not found");
                return;
            } else {
                $this->logger->info("found reservations for room $roomId and origin $origin - " . count($reservations));
            }

            foreach ($reservations as $reservation) {
                //check if reservation is still in the ical
                $this->logger->info("looping reservations  - uid " . $reservation->getUid());
                $isReservationInEvents = false;
                foreach ($events as $event) {
                    $this->logger->info("looping events - uid " . $event['UID']);
                    if (strcmp($event['UID'], $reservation->getUid()) === 0) {
                        $this->logger->info("event and reservation uid match found");
                        $isReservationInEvents = true;
                    }
                }
                //if reservation is not found in ical events then set the status to cancelled
                if (!$isReservationInEvents) {
                    $this->logger->info("updating status to cancelled for reservation not in events - " . $reservation->getUid());
                    $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'cancelled'));
                    $reservation->setStatus($status);
                    $reservationApi->updateReservation($reservation);
                    $this->logger->info("updating status to cancelled done- " . $reservation->getUid());
                }
            }
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
        }
    }


    function iCalDecoder($file): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $ical = file_get_contents($file);
        $this->logger->info("file is: " . $ical);
        preg_match_all('/(BEGIN:VEVENT.*?END:VEVENT)/si', $ical, $result, PREG_PATTERN_ORDER);
        for ($i = 0; $i < count($result[0]); $i++) {
            $tmpbyline = explode("\r\n", $result[0][$i]);

            foreach ($tmpbyline as $item) {
                $this->logger->info("calendar items " . print_r($item, true));
                $tmpholderarray = explode(":", $item);
                if (count($tmpholderarray) > 1) {
                    $this->logger->info("temp holder array " . print_r($tmpholderarray, true));
                    $majorarray[$tmpholderarray[0]] = $tmpholderarray[1];
                }
            }

            $this->logger->info("calendar major array " . print_r($majorarray, true));

            if (preg_match('/DESCRIPTION:(.*)END:VEVENT/si', $result[0][$i], $regs)) {
                $majorarray['DESCRIPTION'] = str_replace("  ", " ", str_replace("\r\n", "", $regs[1]));
            }
            $icalarray[] = $majorarray;
            unset($majorarray);

        }
        $this->logger->info("Ending Method: " . __METHOD__);
        return $icalarray;
    }

    function getRoomIcalUrls($roomId): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            return $this->em->getRepository(Ical::class)->findBy(array('room' => $roomId));
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info(print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    function exportIcalForRoom($roomId): string
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        try {
            $reservationApi = new ReservationApi($this->em, $this->logger);
            $blockedRoomApi = new BlockedRoomApi($this->em, $this->logger);

            $roomApi = new RoomApi($this->em, $this->logger);
            $room = $roomApi->getRoom($roomId);
            if ($room === null) {
                $this->logger->info("room not found for id - " . $roomId);
                return "";
            }
            $roomName = $room->getName();

            $reservations = $reservationApi->getReservationsByRoom($roomId);
            $blockedRooms = $blockedRoomApi->getBlockedRooms($room->getProperty()->getId(), $room->getId());
            $now = new DateTime();

            if ($reservations !== null) {
                $this->logger->info("found reservations - " . count($reservations));
                // create the ical object


                $icalString = 'BEGIN:VCALENDAR
                METHOD:PUBLISH
                PRODID:-//' . $room->getProperty()->getName() . '//Aluve-' . $roomName . '-1// EN
                CALSCALE:GREGORIAN
                VERSION:2.0';

                foreach ($reservations as $reservation) {
                    $this->logger->info("looping reservations - " . $reservation->getId());
                    $resId = $reservation->getId();
                    $event_start = $reservation->getCheckIn()->format('Ymd');
                    $event_end = $reservation->getCheckOut()->format('Ymd');

                    $guestName = $reservation->getGuest()->getName();
                    $guestEmail = $reservation->getGuest()->getEmail();

                    $uid = $reservation->getUid();
                    // date/time is in SQL datetime format

                    $this->logger->info("create the event within the ical object");
                    // create the event within the ical object
                    $icalString .= '
BEGIN:VEVENT
DTEND;VALUE=DATE:' . $event_start . '
DTSTART;VALUE=DATE:' . $event_end . '
DTSTAMP:' . $now->format('Ymd') . 'T100058Z
UID:' . $uid . '
DESCRIPTION:NAME: ' . $guestName . ' \nEMAIL: ' . $guestEmail . '
SUMMARY:' . $room->getProperty()->getName() . ' - ' . $guestName . '  - Resa id: ' . $resId . '
STATUS:CONFIRMED
CREATED:' . $reservation->getReceivedOn()->format('Ymd') . 'T222001Z
END:VEVENT';
                    $this->logger->info("Done creating the event within the ical object");
                }

                $icalString .= '
END:VCALENDAR';
                $this->logger->info($icalString);
                return $icalString;
            }

            if ($blockedRooms !== null) {
                $this->logger->info("found blocked rooms - " . count($blockedRooms));
                // create the ical object

                $icalString = 'BEGIN:VCALENDAR
                METHOD:PUBLISH
                PRODID:-//' . $room->getProperty()->getName() . '//Aluve-' . $roomName . '-1// EN
                CALSCALE:GREGORIAN
                VERSION:2.0';

                foreach ($blockedRooms as $blockedRoom) {
                    $this->logger->info("looping blocked rooms - " . $blockedRoom->getId());

                    $blockRoomId = $blockedRoom->getId();
                    $event_start = $blockedRoom->setFromDate()->format('Ymd');
                    $event_end = $blockedRoom->getToDate()->format('Ymd');

                    $uid = $blockedRoom->getUid();
                    // date/time is in SQL datetime format

                    $this->logger->info("create the event within the ical object");
                    // create the event within the ical object
                    $icalString .= '
BEGIN:VEVENT
DTEND;VALUE=DATE:' . $event_start . '
DTSTART;VALUE=DATE:' . $event_end . '
DTSTAMP:' . $now->format('Ymd') . 'T100058Z
UID:' . $uid . '
DESCRIPTION:NAME: blocked room \nEMAIL: noemail@aluvegh.co.za
SUMMARY:' . $room->getProperty()->getName() . ' - ' . $roomName . '  - Block id: ' . $blockRoomId . '
STATUS:CONFIRMED
CREATED:' . $blockedRoom->getCreatedDate()->format('Ymd') . 'T222001Z
END:VEVENT';
                    $this->logger->info("Done creating the event within the ical object");
                }

                $icalString .= '
END:VCALENDAR';
                $this->logger->info($icalString);
                return $icalString;
            }

        } catch (Exception $ex) {
            $this->logger->info($ex->getMessage());
            return "";
        }
    }
}


