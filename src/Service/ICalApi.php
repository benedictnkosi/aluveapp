<?php

namespace App\Service;

use App\Entity\Config;
use App\Entity\Ical;
use App\Entity\ReservationStatus;
use App\Entity\Rooms;
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

    function importIcalForAllRooms()
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $rooms = $this->em->getRepository(Rooms::class)->findAll();
            foreach ($rooms as $room) {
                $this->importIcalForRoom($room->getId());
            }
            $responseArray[] = array(
                'result_code' => 0,
                'result_message' => 'Successfully imported all reservations'
            );
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }
        return $responseArray;
    }


    function checkForCancellations($events, $roomId, $url): void
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $reservationApi = new ReservationApi($this->em, $this->logger);
        $result = parse_url($url->getLink());
        $origin = $result['host'];
        $reservations = $reservationApi->getReservationsByRoomAndOrigin($roomId, $origin);
        if ($reservations !== null) {
            foreach ($reservations as $reservation) {
                $this->logger->debug("Iterating the reservations " . $reservation->getId());
                $res_uid = $reservation->getUid();
                $isReservationOnEvents = false;
                try {
                    foreach ($events->VEVENT as $event) {
                        $this->logger->debug("Iterating the events " . $event->UID);
                        $event_uid = $event->UID;
                        if (strcmp($res_uid, $event_uid) === 0) {
                            $this->logger->debug("Reservation found on the events ");
                            $isReservationOnEvents = true;
                        }
                    }
                } catch (Exception $ex) {
                    $this->logger->debug("Error looping events " . $ex->getMessage());
                }


                if (!$isReservationOnEvents) {
                    $this->logger->debug("Reservation not found on the events");
                    //cancel reservation if not found on events
                    $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'cancelled'));
                    $reservation->setStatus($status);
                    $this->em->persist($reservation);
                    $this->em->flush($reservation);
                    $this->logger->debug("Reservation successfully cancelled");
                }
            }
        }


    }

    function importIcalForRoom($roomId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        //get all ical Urls for room
        $icals = $this->getRoomIcalUrls($roomId);
        $responseArray = array();

        foreach ($icals as $ical) {
            //read events
            $icalMessagesArray = array();
            $this->logger->debug("trying to get content for   " . $ical->getLink());

            $fileGetPassed = false;
            $count = 0;
            while (!$fileGetPassed && $count < 5) {
                $count++;
                try{
                    $events = VObject\Reader::read(
                        file_get_contents($ical->getLink())
                    );
                    $fileGetPassed = true;
                }catch(Exception $ex){
                    $this->logger->debug("Exception Occurred and the count is $count " . $ex->getMessage());
                }
            }

            if(!$fileGetPassed){
                $responseArray[] = array(
                    'result_code' => 1,
                    'result_message' => 'Failed to get content for link ' . $ical->getLink()
                );
                $this->logger->debug(print_r($responseArray, true));
                $icalMessagesArray[] = array(
                    "ERROR: Failed to get content for link "
                );
                $this->updateIcalLogs($ical, $icalMessagesArray);
                continue;
            }

            try {
                $this->checkForCancellations($events, $roomId, $ical);
                $this->logger->debug("back ");
                $this->logger->debug("events found  " . count($events));
                $i = 0;

                if($events->VEVENT === null){
                    $icalMessagesArray[] = array(
                        "SUCCESS: No events found for link"
                    );
                    $this->updateIcalLogs($ical, $icalMessagesArray);
                    $this->logger->debug("events is null");
                    continue;
                }
                foreach ($events->VEVENT as $event) {
                    $i++;
                    $this->logger->debug("event number $i");

                    $today = date("d.m.Y");
                    $yesterday = new DateTime($today);
                    $interval = new DateInterval('P1D');
                    $yesterday->sub($interval);


                    $date_event = new DateTime($event->DTSTART);
                    $date_end_event = new DateTime($event->DTSTART);
                    $checkInDate = date('Y-m-d', strtotime($event->DTSTART));//user friendly date

                    if ($date_end_event < $today) {
                        $this->logger->debug($date_event->format('Y-m-d H:i:s') . " event in the past ");
                        continue;
                    }

                    $this->logger->debug($date_event->format('Y-m-d H:i:s') . " event In future");
                    $this->logger->debug("url is " . $ical->getLink());
                    $checkOutDate = date('Y-m-d', strtotime($event->DTEND));//user friendly date
                    $summary = $event->SUMMARY;
                    $description = $event->DESCRIPTION;
                    $guestPhoneNumber = "";
                    $uid = $event->UID;
                    $email = "";
                    $guestName = "";

                    $result = parse_url($ical->getLink());
                    $origin = $result['host'];

                    //Airbnb
                    if (str_contains($ical->getLink(), 'airbnb')) {
                        if (str_contains($summary, "Not available")) {
                            $this->logger->debug("Summary is not available for uid " . $uid);
                            $icalMessagesArray[] = array(
                                "SUCCESS: Event ignored as it does not have any information for uid " . $uid
                            );
                            $this->updateIcalLogs($ical, $icalMessagesArray);
                            continue;
                        }
                        $detailsPosition = strpos($description, 'details/');
                        $this->logger->debug("detailsPosition is  " . $detailsPosition);
                        $temp = substr($description, $detailsPosition + strlen('details/'));
                        $this->logger->debug("temp is  " . $temp);
                        $endOfConfirmationPosition = strpos($temp, 'Phone');
                        $this->logger->debug("endOfConfirmationPosition is  " . $endOfConfirmationPosition);
                        $originUrl = trim(substr($temp, 0, $endOfConfirmationPosition));
                        $this->logger->debug("confirmation code is  " . $originUrl);
                    } else if (str_contains($ical->getLink(), 'booking.com')) {
                        $originUrl = $origin;
                        $this->logger->debug("Link is from " . $ical->getLink());
                        $this->logger->debug("Summary: " . $summary);
                        $guestName = $this->getStringByBoundary($summary, 'CLOSED - ', '');
                    } else {
                        $this->logger->debug("Ical Link not mapped");
                        $icalMessagesArray[] = array(
                            "ERROR: Ical Link not mapped - $uid"
                        );
                        continue;
                    }

                    $this->logger->debug($uid . " - " . $guestPhoneNumber);
                    //check if booking already imported
                    $reservationApi = new ReservationApi($this->em, $this->logger);
                    $reservation = $reservationApi->getReservationByUID($uid);

                    //if booking not imported
                    if ($reservation === null) {
                        $this->logger->debug("booking has not been imported");
                        $this->logger->debug("booking has not been imported");

                        //create reservation
                        $response = $reservationApi->createReservation($roomId, $guestName, $guestPhoneNumber, $email, $checkInDate, $checkOutDate, null, $uid, true, $origin, $originUrl);
                        if ($response[0]['result_code'] != 0) {
                            $responseArray[] = array(
                                'result_code' => 0,
                                'result_message' => $response[0]['result_message'] . $uid
                            );
                            $icalMessagesArray[] = array(
                                "ERROR: " . $response[0]['result_message']
                            );
                        } else {
                            $responseArray[] = array(
                                'result_code' => 0,
                                'result_message' => 'Successfully imported reservation ' . $uid
                            );
                            $icalMessagesArray[] = array(
                                "SUCCESS: Successfully imported reservation $uid"
                            );
                        }
                        $this->logger->debug(print_r($responseArray, true));
                    } else {
                        $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));
                        $reservation->setCheckIn(new DateTime($checkInDate));
                        $reservation->setCheckOut(new DateTime($checkOutDate));
                        $reservation->setStatus($status);
                        $this->em->persist($reservation);
                        $this->em->flush($reservation);

                        //block connected Room
                        $blockRoomApi = new BlockedRoomApi($this->em, $this->logger);
                        $this->logger->debug("calling block room to block " . $reservation->getRoom()->getLinkedRoom() . " for room  " . $reservation->getRoom()->getName());
                        $blockRoomApi->blockRoom($reservation->getRoom()->getLinkedRoom(), $checkInDate, $checkOutDate, "Connected Room Booked", $reservation->getId());

                        $responseArray[] = array(
                            'result_code' => 0,
                            'result_message' => 'Successfully updated reservation ' . $uid
                        );
                        $icalMessagesArray[] = array(
                            "SUCCESS: Successfully updated reservation $uid"
                        );
                    }
                    $this->logger->debug(print_r($responseArray, true));
                    $this->updateIcalLogs($ical, $icalMessagesArray);
                }

            } catch (Exception $ex) {
                $this->logger->error($ex->getMessage());
                $this->logger->error($ex->getTraceAsString());

                $icalMessagesArray[] = array(
                    "ERROR: " . $ex->getMessage()
                );
                $this->updateIcalLogs($ical, $icalMessagesArray);
                $this->em->persist($ical);
                $this->em->flush($ical);
                continue;
            }
        }
        return $responseArray;
    }

    function updateIcalLogs($ical, $icalMessagesArray){
        $this->logger->debug("Starting Method: " . __METHOD__);
        $this->logger->debug("icalMessagesArray: " . print_r($icalMessagesArray, true));
        $icalHtmlMessage = "";
        $now = new DateTime();
        foreach($icalMessagesArray as $icalMessage){
            $this->logger->debug("icalMessage: " . print_r($icalMessage, true));
            $class = "logs-success";
            if(str_contains($icalMessage[0], "ERROR")){
                $class = 'logs-error';
            }elseif(str_contains($icalMessage[0], "WARNING")){
                $class = 'logs-warning';
            }
            $icalHtmlMessage .= '<p class="'.$class.'">' . $now->format('Y-m-d H:i:s') . ' - ' . $icalMessage[0] . '</p>';
        }

        $ical->setLogs($icalHtmlMessage);
        $this->em->persist($ical);
        $this->em->flush($ical);
        $this->logger->debug("Ending Method: " . __METHOD__);
    }


    function getStringByBoundary($string, $leftBoundary, $rightBoundary)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $this->logger->debug("Searching for $leftBoundary and $rightBoundary");
        $this->logger->debug($string);
        preg_match('~' . $leftBoundary . '([^?]*)' . $rightBoundary . '~i', $string, $match);
        $this->logger->debug("match is " . $match[1]);
        return $match[1];
    }

    function iCalDecoder($file): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $ical = file_get_contents($file);
        $this->logger->debug("file is: " . $ical);
        preg_match_all('/(BEGIN:VEVENT.*?END:VEVENT)/si', $ical, $result, PREG_PATTERN_ORDER);
        for ($i = 0; $i < count($result[0]); $i++) {
            $tmpbyline = explode("\r\n", $result[0][$i]);

            foreach ($tmpbyline as $item) {
                $this->logger->debug("calendar items " . print_r($item, true));
                $tmpholderarray = explode(":", $item);
                if (count($tmpholderarray) > 1) {
                    $this->logger->debug("temp holder array " . print_r($tmpholderarray, true));
                    $majorarray[$tmpholderarray[0]] = $tmpholderarray[1];
                }
            }

            $this->logger->debug("calendar major array " . print_r($majorarray, true));

            if (preg_match('/DESCRIPTION:(.*)END:VEVENT/si', $result[0][$i], $regs)) {
                $majorarray['DESCRIPTION'] = str_replace("  ", " ", str_replace("\r\n", "", $regs[1]));
            }
            $icalarray[] = $majorarray;
            unset($majorarray);

        }
        $this->logger->debug("Ending Method: " . __METHOD__);
        return $icalarray;
    }

    function getRoomIcalUrls($roomId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            return $this->em->getRepository(Ical::class)->findBy(array('room' => $roomId));
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    function exportIcalForRoom($roomId): string
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {

            $reservationApi = new ReservationApi($this->em, $this->logger);
            $blockedRoomApi = new BlockedRoomApi($this->em, $this->logger);

            $roomApi = new RoomApi($this->em, $this->logger);
            $room = $roomApi->getRoom($roomId);
            if ($room === null) {
                $this->logger->debug("room not found for id - " . $roomId);
                return "";
            }
            $roomName = $room->getName();

            $reservations = $reservationApi->getReservationsByRoom($roomId);
            $blockedRooms = $blockedRoomApi->getBlockedRooms($room->getProperty()->getUid(), $room->getId());
            $now = new DateTime();

            //do not fix formatting for this line
            $icalString = 'BEGIN:VCALENDAR
METHOD:PUBLISH
PRODID:-//' . $room->getProperty()->getName() . '//Aluve-' . $roomName . '-1// EN
CALSCALE:GREGORIAN
VERSION:2.0';

            if ($reservations !== null) {
                $this->logger->debug("found reservations - " . count($reservations));
                // create the ical object

                foreach ($reservations as $reservation) {
                    $this->logger->debug("looping reservations - " . $reservation->getId());
                    $resId = $reservation->getId();
                    $event_start = $reservation->getCheckIn()->format('Ymd');
                    $event_end = $reservation->getCheckOut()->format('Ymd');

                    $guestName = $reservation->getGuest()->getName();
                    $guestEmail = $reservation->getGuest()->getEmail();

                    $uid = $reservation->getUid();
                    // date/time is in SQL datetime format

                    $this->logger->debug("create the event within the ical object");
                    // create the event within the ical object
                    $icalString .= '
BEGIN:VEVENT
DTEND;VALUE=DATE:' . $event_end . '
DTSTART;VALUE=DATE:' . $event_start . '
DTSTAMP:' . $now->format('Ymd') . 'T100058Z
UID:' . $uid . '
DESCRIPTION:NAME: ' . $guestName . ' \nEMAIL: ' . $guestEmail . '
SUMMARY:' . $room->getProperty()->getName() . ' - ' . $guestName . '  - Resa id: ' . $resId . '
STATUS:CONFIRMED
CREATED:' . $reservation->getReceivedOn()->format('Ymd') . 'T222001Z
END:VEVENT';
                    $this->logger->debug("Done creating the event within the ical object");
                }


                $this->logger->debug($icalString);

            }

            if ($blockedRooms !== null) {
                $this->logger->debug("found blocked rooms - " . count($blockedRooms));
                // create the ical object

                foreach ($blockedRooms as $blockedRoom) {
                    $this->logger->debug("looping blocked rooms - " . $blockedRoom->getId());

                    $blockRoomId = $blockedRoom->getId();
                    $event_start = $blockedRoom->getFromDate()->format('Ymd');
                    $event_end = $blockedRoom->getToDate()->format('Ymd');

                    $uid = $blockedRoom->getUid();
                    // date/time is in SQL datetime format

                    $this->logger->debug("create the event within the ical object");
                    // create the event within the ical object
                    $icalString .= '
BEGIN:VEVENT
DTEND;VALUE=DATE:' . $event_end . '
DTSTART;VALUE=DATE:' . $event_start . '
DTSTAMP:' . $now->format('Ymd') . 'T100058Z
UID:' . $uid . '
DESCRIPTION:NAME: blocked room \nEMAIL: noemail@aluvegh.co.za
SUMMARY:' . $room->getProperty()->getName() . ' - ' . $roomName . '  - Block id: ' . $blockRoomId . '
STATUS:CONFIRMED
CREATED:' . $blockedRoom->getCreatedDate()->format('Ymd') . 'T222001Z
END:VEVENT';
                    $this->logger->debug("Done creating the event within the ical object");
                }

                $this->logger->debug($icalString);

            }

            $icalString .= '
END:VCALENDAR';

        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            return "";
        }
        return $icalString;
    }

    function getAirbnbEmailAndPassword(): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $configs = $this->em->getRepository(Config::class)->findAll();
            foreach ($configs as $config) {
                $responseArray[] = array(
                    'email' => $config->getAirbnbEmail(),
                    'password' => $config->getAirbnbEmailPassword(),
                    'result_code' => 0
                );
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    function addNewChannel($roomId, $link): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            if (!str_contains($link, "airbnb.com") && !str_contains($link, "booking.com") && !str_contains($link, "airbnb.co.za")) {
                $responseArray[] = array(
                    'result_message' => 'Only booking.com and airbnb channels are allowed, Please contact admin to add new channel',
                    'result_code' => 1
                );
                return $responseArray;
            }
            //check that the limit for number of calenders per room is not reached
            $iCalLinksForRoom = $this->em->getRepository(Ical::class)->findBy(array('room' => $roomId));
            if (count($iCalLinksForRoom) > ICAL_LIMIT_PER_ROOM) {
                $responseArray[] = array(
                    'result_message' => 'You have reached the limit of ' . ICAL_LIMIT_PER_ROOM . ' for channels per room',
                    'result_code' => 1
                );
                return $responseArray;
            }
            $iCalLink = $this->em->getRepository(Ical::class)->findOneBy(array('link' => $link));
            $room = $this->em->getRepository(Rooms::class)->findOneBy(array('id' => $roomId));
            if ($iCalLink !== null) {
                $responseArray[] = array(
                    'result_message' => 'Channel with the same link already added',
                    'result_code' => 1
                );
            } else {
                $ical = new Ical();
                $result = parse_url($link);
                if (!isset($result['host'])) {
                    $responseArray[] = array(
                        'result_message' => 'Link not a url',
                        'result_code' => 1
                    );
                } else {
                    $ical->setName($result['host']);
                    $ical->setRoom($room);
                    $ical->setLink($link);
                    $this->em->persist($ical);
                    $this->em->flush($ical);

                    $responseArray[] = array(
                        'result_message' => 'Successfully added channel',
                        'result_code' => 0,
                        'id' => $ical->getId()
                    );
                }

            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    function getIcalLinks($roomId): ?array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            return $this->em->getRepository(Ical::class)->findBy(array('room' => $roomId));
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return null;
    }

    function removeIcalLink($icalId): ?array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $ical = $this->em->getRepository(Ical::class)->findOneBy(array('id' => $icalId));
            if ($ical !== null) {
                $this->em->remove($ical);
                $this->em->flush($ical);
                $responseArray[] = array(
                    'result_message' => 'Successfully removed channel',
                    'result_code' => 0
                );
            } else {
                $responseArray[] = array(
                    'result_message' => 'Channel not found, please refresh page',
                    'result_code' => 1
                );
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }


    /**
     * @throws \Google\Exception
     */
    function updateAirbnbGuestUsingGmail($guestApi): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();

        $communicationApi = new CommunicationApi($this->em, $this->logger);
        $emails = $communicationApi->getAirbnbConfirmationEmails();

        if ($emails) {
            foreach ($emails as $email) {
                $emailSubject = $email['subject'];
                echo "found emails";

                $this->logger->debug("Email subject is " . $emailSubject);
                try {
                    $pos = strpos($emailSubject, 'Reservation confirmed');
                    if ($pos !== false) {
                        $bodyText = $email['body'];
                        $messageThreadId = trim($this->getStringByBoundary($bodyText, 'hosting/thread/', '?'));
                        $this->logger->debug("message thread is " . $messageThreadId);
                        //$bodyText = quoted_printable_decode($bodyText);
                        $guestName = trim($this->getStringByBoundary($emailSubject, 'Reservation confirmed - ', ' arrives '));
                        $confirmationCode = trim($this->getStringByBoundary($bodyText, 'reservations/details/', '?'));
                        $result = $guestApi->createAirbnbGuest($confirmationCode, $guestName);
                        $responseArray = array(
                            'result_code' => 0,
                            'result_description' => $result
                        );
                        $this->logger->debug(print_r($responseArray, true));
                    }
                } catch (\Throwable $e) {
                    $responseArray = array(
                        'result_code' => 1,
                        'result_description' => $e->getMessage()
                    );

                    $this->logger->debug(print_r($responseArray, true));
                }
            }
        } else {
            $responseArray = array(
                'result_code' => 0,
                'result_description' => "no emails found"
            );

            $this->logger->debug(print_r($responseArray, true));
        }

        return $responseArray;
    }
}


