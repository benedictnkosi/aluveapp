<?php

namespace App\Service;

use App\Entity\FailedUids;
use App\Entity\Reservations;
use App\Entity\ReservationStatus;
use App\Entity\Rooms;
use App\Helpers\SMSHelper;
use DateInterval;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

require_once(__DIR__ . '/../app/application.php');

class ReservationApi
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        if (session_id() === '') {
            $logger->info("Session id is empty");
            session_start();
        }
    }

    public function getReservation($resId)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            return $this->em->getRepository(Reservations::class)->findOneBy(array('id' => $resId));
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }
        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function getReservationJson($resId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $reservation = $this->em->getRepository(Reservations::class)->findOneBy(array('id' => $resId));
            if ($reservation === null) {
                $responseArray[] = array(
                    'result_message' => "Reservation not found for id $resId",
                    'result_code' => 1
                );

            } else {
                $paymentApi = new PaymentApi($this->em, $this->logger);
                $payments = $paymentApi->getReservationPayments($reservation->GetId());
                $paymentsHtml = "";
                $totalPayment = 0;
                foreach ($payments as $payment) {
                    $paymentsHtml .= '<p class="small-font-italic"> ' . $payment->getDate()->format("d-M") . ' - R' . number_format((float)$payment->getAmount(), 2, '.', '') . '</p>';
                    $totalPayment += (intVal($payment->getAmount()));
                }

                $responseArray[] = array(
                    'id' => $reservation->GetId(),
                    'check_in' => $reservation->getCheckIn()->format('Y-m-d'),
                    'check_out' => $reservation->getCheckOut()->format('Y-m-d'),
                    'status' => $reservation->getStatus()->getId(),
                    'guest_name' => $reservation->getGuest()->getName(),
                    'guest_id' => $reservation->getGuest()->getId(),
                    'check_in_status' => $reservation->getCheckInStatus(),
                    'check_in_time' => $reservation->getCheckInTime(),
                    'check_out_time' => $reservation->getCheckOutTime(),
                    'checked_in_time' => $reservation->getCheckedInTime(),
                    'room_id' => $reservation->getRoom()->getId(),
                    'room_name' => $reservation->getRoom()->getName(),
                    'total_paid' => $totalPayment,
                    'guest_phone_number' => $reservation->getGuest()->getPhoneNumber(),
                    'result_code' => 0
                );
            }


            return $responseArray;
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }
        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }


    public function getReservationByUID($uid)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));
            return $this->em->getRepository(Reservations::class)->findOneBy(array('uid' => $uid, 'status' => $status));
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }
        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return null;
    }

    public function getPendingReservations()
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $reservations = null;
        try {
            $datetime = new DateTime('today');
            $datetime->sub(new DateInterval('P1D'));

            $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'pending'));

            $reservations = $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
                JOIN r.room a
                JOIN a.property p
            WHERE p.id = " . $_SESSION['PROPERTY_ID'] . "
            and r.checkIn >= '" . $datetime->format('Y-m-d') . "'
            and r.status = '" . $status->getId() . "'
            order by r.checkIn asc")
                ->getResult();

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }
        $this->logger->debug("Ending Method before the return: " . __METHOD__);

        if (empty($reservations)) {
            return null;
        }
        return $reservations;
    }

    public function getUpComingReservations($roomId = 0, $includeOpened = false, $includeStayOvers = false)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $reservations = null;
        try {
            $roomFilter = "";
            if ($roomId != 0) {
                $roomFilter = " and r.room = $roomId ";
            }
            $now = new DateTime();
            $datetime = new DateTime();
            $maxFutureDate = $datetime->add(new DateInterval('P180D'));
            $confirmedStatus = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));
            $openedStatus = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'opened'));

            $excludeStayOverSql = "and r.checkIn >= '" . $now->format('Y-m-d') . "'";
            if ($includeStayOvers) {
                $excludeStayOverSql = "";
            }

            $includeOpenedSql = "and (r.status = '" . $confirmedStatus->getId() . "') ";
            if ($includeOpened) {
                $includeOpenedSql = "and (r.status = '" . $confirmedStatus->getId() . "' or r.status = '" . $openedStatus->getId() . "' ) ";
            }
            $reservations = $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
                JOIN r.room a
                JOIN a.property p
            WHERE p.id = " . $_SESSION["PROPERTY_ID"] . "
            and r.checkIn <= '" . $maxFutureDate->format('Y-m-d') . "'
            and r.checkOut >= '" . $now->format('Y-m-d') . "'
            $excludeStayOverSql 
            $roomFilter  
            $includeOpenedSql
            order by r.checkIn asc ")
                ->getResult();
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }
        $this->logger->debug("Ending Method before the return: " . __METHOD__);

        if (empty($reservations)) {
            return null;
        }
        return $reservations;
    }

    public function getPastReservations()
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $reservations = null;
        try {
            $datetime = new DateTime();
            $now = new DateTime('today midnight');
            $maxPastDate = $now->sub(new DateInterval('P180D'));
            $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));

            $reservations = $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
                JOIN r.room a
                JOIN a.property p
            WHERE p.id = " . $_SESSION['PROPERTY_ID'] . "
            and r.checkOut < '" . $datetime->format('Y-m-d') . "'
            and r.checkIn > '" . $maxPastDate->format('Y-m-d') . "'
            and r.status = '" . $status->getId() . "'
            order by r.checkOut desc")
                ->getResult();
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }


        $this->logger->debug("Ending Method before the return: " . __METHOD__);

        if (empty($reservations)) {
            return null;
        }

        return $reservations;
    }

    public function getCheckOutReservation($propertyId)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $reservations = null;
        try {
            $datetime = new DateTime();
            $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));


            $reservations = $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
                JOIN r.room a
                JOIN a.property p
            where p.id = " . $propertyId . "
            and r.checkOut = '" . $datetime->format('Y-m-d') . "'
            and r.status = '" . $status->getId() . "'
            order by r.checkOut desc")
                ->getResult();


        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }
        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        if (empty($reservations)) {
            return null;
        }

        return $reservations;
    }

    public function getReservationsByRoomAndDaysToCheckIn($roomId, $days)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            $now = new DateTime('today midnight');
            $checkInDate = $now->add(new DateInterval("P" . $days . "D"));
            $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));

            $reservations = $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
            WHERE r.checkIn = '" . $checkInDate->format('Y-m-d') . "'
            and r.room = $roomId 
            and r.status = " . $status->getId())
                ->getResult();

            if (empty($reservations)) {
                return null;
            }

            return $reservations;

        } catch (Exception $exception) {
            return null;
        }
    }

    public function getReservationsByRoomAndDaysAfterCheckOut($roomId, $days)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            $now = new DateTime('today midnight');
            $checkOutDate = $now->sub(new DateInterval("P" . ($days * -1) . "D"));
            $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));

            $query = $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
            WHERE r.checkOut = '" . $checkOutDate->format('Y-m-d') . "'
            and r.room = $roomId 
            and r.status = " . $status->getId());

            $this->logger->debug("before query " . $query->getDQL());

            $reservations = $query->getResult();


            if (empty($reservations)) {
                return null;
            }

            return $reservations;

        } catch (Exception $ex) {
            $this->logger->debug($ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString());
            return null;
        }
    }

    public function getReservationsByOriginalRoomAndOrigin($roomId, $origin)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            $now = new DateTime('today midnight');
            $yesterdayDate = $now->sub(new DateInterval("P1D"));
            $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));

            $this->logger->debug("query " . $roomId . " " . $origin);

            $reservations = $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
            WHERE r.checkIn > '" . $yesterdayDate->format('Y-m-d') . "'
            and r.originalRoom = $roomId 
            and r.origin = '" . $origin . "'
            and r.status = " . $status->getId())
                ->getResult();

            if (empty($reservations)) {
                return null;
            }

            return $reservations;
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage() . " " . $ex->getTraceAsString());
            return null;
        }
    }

    public function getReservationsByRoom($roomId)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            $now = new DateTime('today midnight');
            $maxPastDate = $now->sub(new DateInterval("P" . ICAL_PAST_DAYS . "D"));
            $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));

            $reservations = $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
            WHERE r.checkIn > '" . $maxPastDate->format('Y-m-d') . "'
            and r.room = $roomId 
            and r.status = " . $status->getId())
                ->getResult();

            if (empty($reservations)) {
                return null;
            }

            return $reservations;
        } catch (Exception $exception) {
            return null;
        }
    }

    public function getReservationsByGuest($guestId)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            return $this->em->getRepository(Reservations::class)->findOneBy(array('guest' => $guestId));
        } catch (Exception) {
            return null;
        }
    }


    public function getStayOversReservations()
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $reservations = null;
        try {
            $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));
            $reservations = $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
                JOIN r.room a
                JOIN a.property p
            where p.id = " . $_SESSION['PROPERTY_ID'] . "
            and r.checkIn < CURRENT_DATE() 
            And r.checkOut > CURRENT_DATE() 
            and r.status = " . $status->getId() . "
            order by r.checkIn asc")
                ->getResult();

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        if (empty($reservations)) {
            return null;
        }

        return $reservations;
    }

    public function updateReservation($reservation): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);

        $responseArray = array();
        try {
            $this->em->persist($reservation);
            $this->em->flush($reservation);

            $responseArray[] = array(
                'result_code' => 0,
                'result_message' => 'Successfully updated reservation'
            );

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_code' => 1,
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function updateReservationDate($reservation, $checkInDate, $checkOutDate, $blockedRoomApi): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);

        $responseArray = array();
        try {
            $roomApi = new RoomApi($this->em, $this->logger);
            $isRoomAvailable = $roomApi->isRoomAvailable($reservation->getRoom()->getId(), $checkInDate, $checkOutDate, $reservation->getId());
            if ($isRoomAvailable) {
                $reservation->setCheckIn(new DateTime($checkInDate));
                $reservation->setCheckOut(new DateTime($checkOutDate));
                $reservation->setCheckOut(new DateTime($checkOutDate));
                $reservation->setUid(uniqid() . "@" . SERVER_NAME);

                $this->em->persist($reservation);
                $this->em->flush($reservation);

                //update blocked room
                $blockedRoomApi->updateBlockedRoomByReservation($reservation->getId(), $checkInDate, $checkOutDate);
            } else {
                $responseArray[] = array(
                    'result_code' => 1,
                    'result_message' => 'Selected dates not available'
                );
                return $responseArray;
            }

            $responseArray[] = array(
                'result_code' => 0,
                'result_message' => 'Successfully updated reservation'
            );

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_code' => 1,
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function updateReservationRoom($reservation, $roomId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);

        $responseArray = array();
        try {
            $roomApi = new RoomApi($this->em, $this->logger);
            $isRoomAvailable = $roomApi->isRoomAvailable($roomId, $reservation->getCheckIn()->format("Y-m-d"), $reservation->getCheckOut()->format("Y-m-d"), $reservation->getId());
            if ($isRoomAvailable) {
                $room = $roomApi->getRoom($roomId);
                $reservation->setRoom($room);
                $this->em->persist($reservation);
                $this->em->flush($reservation);
            } else {
                $responseArray[] = array(
                    'result_code' => 1,
                    'result_message' => 'Selected dates not available'
                );
                return $responseArray;
            }

            $responseArray[] = array(
                'result_code' => 0,
                'result_message' => 'Successfully updated reservation'
            );

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_code' => 1,
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }


    public function updateReservationOriginUrl($reservation, $confirmationCode): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);

        $responseArray = array();
        try {
            $reservation->setOriginUrl($confirmationCode);
            $this->em->persist($reservation);
            $this->em->flush($reservation);

            $responseArray[] = array(
                'result_code' => 0,
                'result_message' => 'Successfully updated reservation'
            );

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_code' => 1,
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function createReservation($roomIds, $guestName, $phoneNumber, $email, $checkInDate, $checkOutDate, $request = null, $adultGuests = null, $childGuests = null, $uid = null, $isImport = false, $origin = "website", $originUrl = "website"): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $this->logger->debug("room ids" . $roomIds);
        $responseArray = array();
        $blockRoomApi = new BlockedRoomApi($this->em, $this->logger);
        $room = null;
        try {
            //get property Id
            $roomIds = str_replace('[', "", $roomIds);
            $roomIds = str_replace(']', "", $roomIds);
            $roomIds = str_replace('"', "", $roomIds);
            $roomIdsArray = explode(",", $roomIds);
            $reservationIds = array();
            foreach ($roomIdsArray as $roomId) {
                $this->logger->debug("room id " . $roomId);
                $roomApi = new RoomApi($this->em, $this->logger);
                //get guest
                $guestApi = new GuestApi($this->em, $this->logger);
                $guest = null;
                //get room

                $room = $roomApi->getRoom($roomId);

                if (strcmp($origin, "airbnb.com") === 0) {
                    $guest = $guestApi->getGuestByName("Airbnb Guest");
                } elseif (strcmp($origin, "booking.com") === 0) {
                    if (!empty($phoneNumber)) {
                        $guest = $guestApi->getGuestByPhoneNumber($phoneNumber, $request, $room->getProperty()->getId());
                    }
                } elseif (strlen($phoneNumber) > 1) {
                    $guest = $guestApi->getGuestByPhoneNumber($phoneNumber, $request, $room->getProperty()->getId());
                }


                if ($guest == null) {
                    $this->logger->debug("guest not found, creating a new guest");
                    //create guest
                    $response = $guestApi->createGuest($guestName, $phoneNumber, $email, $origin, $room->getProperty()->getId());
                    if ($response[0]['result_code'] != 0) {
                        $this->logger->debug(print_r($response, true));

                        if ($isImport) {
                            //email admin person
                            if (!$this->isFailedUidRecorded($uid)) {
                                $this->recordFailedUid($uid);

                                $messageBody = "There was a problem creating a reservation. failed to create guest entity. " . $checkInDate . " - " . $room->getName();
                                $SMSHelper = new SMSHelper($this->logger);
                                $SMSHelper->sendMessage("+27837917430", $messageBody);
                                $SMSHelper->sendMessage(str_replace(" ", "", $room->getProperty()->getPhoneNumber()), $messageBody);

                            }

                        }
                        return $response;
                    } else {
                        $guest = $response[0]['guest'];
                    }
                } else {
                    if (strcmp($guest->getState(), "blocked") === 0) {
                        $responseArray[] = array(
                            'result_code' => 1,
                            'result_message' => 'Guest blocked for ' . $guest->getComments()
                        );

                        if ($isImport) {
                            //email admin person
                            if (!$this->isFailedUidRecorded($uid)) {
                                $this->recordFailedUid($uid);
                                $communicationApi = new CommunicationApi($this->em, $this->logger);

                                $messageBody = "There was a problem creating a reservation. Guest Blocked. " . $checkInDate . " - " . $room->getName();
                                $SMSHelper = new SMSHelper($this->logger);
                                $SMSHelper->sendMessage("+27837917430", $messageBody);
                                $SMSHelper->sendMessage(str_replace(" ", "", $room->getProperty()->getPhoneNumber()), $messageBody);
                            }
                        }

                        return $responseArray;
                    }
                }

                //check if room is available
                $isRoomAvailable = $roomApi->isRoomAvailable($room->getId(), $checkInDate, $checkOutDate);

                if (!$isRoomAvailable) {
                    $responseArray[] = array(
                        'result_code' => 1,
                        'result_message' => 'Tried to create\import a reservation. Room not available for selected dates ' . $checkInDate . " - " . $checkOutDate
                    );

                    if ($isImport) {
                        //email admin person
                        if (!$this->isFailedUidRecorded($uid)) {
                            $this->recordFailedUid($uid);

                            $messageBody = "There was a problem importing a reservation. " . $checkInDate . " - " . $room->getName() . "#" . $origin;
                            $SMSHelper = new SMSHelper($this->logger);
                            $SMSHelper->sendMessage("+27837917430", $messageBody);
                            $SMSHelper->sendMessage(str_replace(" ", "", $room->getProperty()->getPhoneNumber()), $messageBody);
                        }

                    }else{
                        return $responseArray;
                    }
                }

                $reservation = new Reservations();
                $reservation->setRoom($room);
                $reservation->setOriginalRoom($room);
                $reservation->setAdditionalInfo("Guest Name is: " . $guest->getName());
                $reservation->setCheckIn(new DateTime($checkInDate));
                $reservation->setCheckOut(new DateTime($checkOutDate));
                $reservation->setGuest($guest);

                $reservation->setOrigin($origin);
                $reservation->setReceivedOn(new DateTime());
                $reservation->setUpdatedOn(new DateTime());
                $reservation->setCheckedInTime(NULL);
                $reservation->setOriginUrl($originUrl);
                $reservation->setAdults($adultGuests);
                $reservation->setChildren($childGuests);

                if ($isImport) {
                    $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));
                } else {
                    $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'pending'));
                }

                $reservation->setStatus($status);

                if ($uid == null) {
                    $reservation->setUid(uniqid() . "@" . SERVER_NAME);
                } else {
                    $reservation->setUid($uid);
                }

                $this->em->persist($reservation);
                $this->em->flush($reservation);


                //block connected Room
                if ($isImport) {
                    $this->logger->debug("calling block room to block " . $room->getLinkedRoom() . " for room  " . $room->getName());
                    $blockRoomApi->blockRoom($room->getLinkedRoom(), $checkInDate, $checkOutDate, "Connected Room Booked ", $reservation->getId());
                }

                //check google ads notification
                $now = new DateTime();
                if (strcmp($reservation->getCheckIn()->format("Y-m-d"), $now->format("Y-m-d")) === 0) {
                    $notificationApi = new NotificationApi($this->em, $this->logger);
                    $notificationApi->updateAdsNotification($room->getProperty()->getId());
                }


                if (!$isImport) {
                    //send SMS
                    if (str_starts_with($reservation->getGuest()->getPhoneNumber(), '0') || str_starts_with($reservation->getGuest()->getPhoneNumber(), '+27')) {
                        $this->logger->debug("this is a south african number " . $reservation->getGuest()->getPhoneNumber());
                        $SMSHelper = new SMSHelper($this->logger);
                        $message = "Hi, Invoice http://" . $reservation->getRoom()->getProperty()->getServerName() . "/invoice.html?id=" . $reservation->getId() . " - Booking confirmed once payment reflects. Use Payshap for instant payments. No children allowed.";
                        $SMSHelper->sendMessage($guest->getPhoneNumber(), $message);
                    }

                    //Send email
                    $this->logger->debug("this reservation is not an import");
                    if (!empty($reservation->getGuest()->getEmail())) {
                        $this->logger->debug("user email is not empty sending email" . $reservation->getGuest()->getEmail());
                        $emailBody = file_get_contents(__DIR__ . '/../email_template/new_reservation.html');
                        $emailBody = str_replace("guest_name", $reservation->getGuest()->getName(), $emailBody);
                        $emailBody = str_replace("check_in", $reservation->getCheckIn()->format("d M Y"), $emailBody);
                        $emailBody = str_replace("check_out", $reservation->getCheckOut()->format("d M Y"), $emailBody);
                        $emailBody = str_replace("server_name", $reservation->getRoom()->getProperty()->getServerName(), $emailBody);
                        $emailBody = str_replace("reservation_id", $reservation->getId(), $emailBody);
                        $emailBody = str_replace("property_name", $reservation->getRoom()->getProperty()->getName(), $emailBody);
                        $emailBody = str_replace("room_name", $reservation->getRoom()->getName(), $emailBody);

                        $this->logger->debug("email body" . $emailBody);


                        $communicationApi = new CommunicationApi($this->em, $this->logger);
                        $communicationApi->sendEmailViaGmail(ALUVEAPP_ADMIN_EMAIL, $reservation->getGuest()->getEmail(), $emailBody, $reservation->getRoom()->getProperty()->getName() . '- Thank you for your reservation', $reservation->getRoom()->getProperty()->getName(), $reservation->getRoom()->getProperty()->getEmailAddress());
                        $this->logger->debug("Successfully sent email to guest");
                    } else {
                        $this->logger->debug("user email is empty not sending email" . $reservation->getGuest()->getEmail());
                    }
                    $reservationIds[] = $reservation->getId();
                }
            }
            $responseArray[] = array(
                'result_code' => 0,
                'result_message' => "Successfully created reservation",
                'reservation_id' => $reservationIds
            );
        } catch
        (Exception $ex) {
            $responseArray[] = array(
                'result_code' => 1,
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
            );
            $this->logger->debug(print_r($responseArray, true));
            if ($isImport) {
                //email admin person
                if (!$this->isFailedUidRecorded($uid)) {
                    $this->recordFailedUid($uid);

                    $messageBody = "There was an exception creation a reservation. " . $checkInDate . " - " . $room->getName() ;
                    $SMSHelper = new SMSHelper($this->logger);
                    $SMSHelper->sendMessage("+27837917430", $messageBody);
                    $SMSHelper->sendMessage(str_replace(" ", "", $room->getProperty()->getPhoneNumber()) , $messageBody);
                }

            }
        }


        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function recordFailedUid($uid)
    {
        $failedUid = new FailedUids();
        $failedUid->setUid($uid);
        $failedUid->setDate(new DateTime());
        $this->em->persist($failedUid);
        $this->em->flush($failedUid);
    }

    public function isFailedUidRecorded($uid): bool
    {
        $failedUid = $this->em->getRepository(FailedUids::class)->findOneBy(array('uid' => $uid));
        if ($failedUid === null) {
            return false;
        } else {
            return true;
        }
    }


    public function isEligibleForCheckIn($reservation): bool
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $isEligible = true;
        if ($reservation->getGuest()->getIdNumber() == null) {
            $isEligible = false;
        }

        if (strcasecmp($reservation->getGuest()->getPhoneNumber(), "") == 0) {
            $isEligible = false;
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $isEligible;
    }

    public function getAmountDue($reservation): float|int
    {
        $this->logger->debug("Starting Method: " . __METHOD__);

        $addOnsApi = new AddOnsApi($this->em, $this->logger);
        $paymentApi = new PaymentApi($this->em, $this->logger);

        $addOns = $addOnsApi->getReservationAddOns($reservation->getId());
        $totalDays = intval($reservation->getCheckIn()->diff($reservation->getCheckOut())->format('%a'));
        $totalPriceForAllAdOns = 0;
        foreach ($addOns as $addOn) {
            $totalPriceForAllAdOns += (intVal($addOn->getAddOn()->getPrice()) * intval($addOn->getQuantity()));
        }

        $roomPrice = 0;
        if (strcasecmp($reservation->getOrigin(), "website") == 0) {
            $roomPrice = $reservation->getRoom()->getPrice();
        }

        $totalPrice = intval($roomPrice) * $totalDays;
        $totalPrice += $totalPriceForAllAdOns;

        //payments
        $payments = $paymentApi->getReservationPayments($reservation->getId());
        $totalPayment = 0;
        foreach ($payments as $payment) {
            $totalPayment += (intVal($payment->getAmount()));
        }

        $due = $totalPrice - $totalPayment;
        $this->logger->debug("Total Add Ons: " . $totalPriceForAllAdOns);
        $this->logger->debug("Room Price: " . $roomPrice);
        $this->logger->debug("Total Days: " . $totalDays);
        $this->logger->debug("Total Price: " . $totalPrice);
        $this->logger->debug("Total Paid: " . $totalPayment);
        $this->logger->debug("due: " . $due);

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $due;
    }


    public function sendReviewRequest($propertyId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);

        $responseArray = array();
        try {
            $reservations = $this->getCheckOutReservation($propertyId);
            if ($reservations != null) {
                foreach ($reservations as $reservation) {
                    //send email if provided
                    if (!empty($reservation->getGuest()->getEmail())) {
                        $this->sendReviewEmail($reservation);
                    } else {
                        //send sms
                        if (str_starts_with($reservation->getGuest()->getPhoneNumber(), '0') || str_starts_with($reservation->getGuest()->getPhoneNumber(), '+27')) {
                            $this->logger->debug("this is a south african number " . $reservation->getGuest()->getPhoneNumber());
                            $SMSHelper = new SMSHelper($this->logger);
                            $message = "Hi " . $reservation->getGuest()->getName() . ", Thank you for staying with us. Please take a few seconds to give us a 5-star review on Google. " . $reservation->getRoom()->getProperty()->getGoogleReviewLink();
                            $SMSHelper->sendMessage($reservation->getGuest()->getPhoneNumber(), $message);
                        }
                    }

                    $this->logger->debug(print_r($responseArray, true));
                }
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_code' => 1,
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }


    function sendReviewEmail($reservation): bool
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            if ($reservation->getRoom()->getProperty()->getGoogleReviewLink() != null) {
                //send email to guest
                $emailBody = file_get_contents(__DIR__ . '/../email_template/review_request.html');
                $emailBody = str_replace("guest_name", $reservation->getGuest()->getName(), $emailBody);
                $emailBody = str_replace("google_review_link", $reservation->getRoom()->getProperty()->getGoogleReviewLink(), $emailBody);
                $emailBody = str_replace("property_name", $reservation->getRoom()->getProperty()->getName(), $emailBody);

                $communicationApi = new CommunicationApi($this->em, $this->logger);
                $communicationApi->sendEmailViaGmail(ALUVEAPP_ADMIN_EMAIL, $reservation->getGuest()->getEmail(), $emailBody, $reservation->getRoom()->getProperty()->getName() . ' - Please review us', $reservation->getRoom()->getProperty()->getName(), $reservation->getRoom()->getProperty()->getEmailAddress());
            }
            return true;
        } catch (Exception $ex) {
            $this->logger->debug(print_r($ex, true));
            return false;
        }
    }

    public function isAllRoomsBooked($propertyId): bool
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $roomApi = new RoomApi($this->em, $this->logger);


        $rooms = $this->em->getRepository(Rooms::class)->findBy(array('property' => $propertyId, 'status' => 1));
        foreach ($rooms as $room) {
            $now = new DateTime();
            if ($roomApi->isRoomAvailable($room->getId(), $now->format('Y-m-d'), $now->add(new DateInterval("P1D"))->format('Y-m-d'))) {
                return false;
            }
        }
        return true;
    }


}