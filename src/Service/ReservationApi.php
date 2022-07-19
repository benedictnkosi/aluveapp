<?php

namespace App\Service;

use App\Entity\Reservations;
use App\Entity\ReservationStatus;
use App\Helpers\Invoice;
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
        if(session_id() === ''){
            $logger->info("Session id is empty");
            session_start();
        }
    }

    public function getReservation($resId)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            return $this->em->getRepository(Reservations::class)->findOneBy(array('id' => $resId));
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

    public function getReservationByUID($uid)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            return $this->em->getRepository(Reservations::class)->findOneBy(array('uid' => $uid));
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info(print_r($responseArray, true));
        }
        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return null;
    }

    public function getPendingReservations($propertyUid)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $datetime = new DateTime('today');
        $datetime->sub(new DateInterval('P1D'));

        $status =  $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'pending'));

        $reservations = $this->em
            ->createQuery("SELECT r FROM App\Entity\Reservations r 
                JOIN r.room a
                JOIN a.property p
            WHERE p.uid = '".$propertyUid."'
            and r.checkIn > '" . $datetime->format('Y-m-d') . "'
            and r.status = '".$status->getId()."'
            order by r.checkIn asc")
            ->getResult();;
        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $reservations;
    }

    public function getUpComingReservations($propertyUid, $roomId = 0)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $reservations = null;
        try {
            $roomFilter = "";
            if ($roomId != 0) {
                $roomFilter = " and r.room = $roomId ";
            }
            $now = new DateTime();
            $datetime = new DateTime();
            $maxFutureDate = $datetime->add(new DateInterval('P180D'));
            $status =  $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));

            $reservations = $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
                JOIN r.room a
                JOIN a.property p
            WHERE p.uid = '".$propertyUid."'
            and r.checkIn <= '".$maxFutureDate->format('Y-m-d')."'
            and r.checkOut > '".$now->format('Y-m-d')."'
            and r.checkIn >= '".$now->format('Y-m-d')."'
                    $roomFilter 
            and r.status = '".$status->getId()."'
            order by r.checkIn asc ")
                ->getResult();
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info(print_r($responseArray, true));
        }
        $this->logger->info("Ending Method before the return: " . __METHOD__);

        return $reservations;
    }

    public function getPastReservations($propertyUid): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $reservations = "";
        try {
            $datetime = new DateTime();
            $now = new DateTime('today midnight');
            $maxPastDate = $now->sub(new DateInterval('P180D'));
            $status =  $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));

            $reservations = $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
                JOIN r.room a
                JOIN a.property p
            WHERE p.uid = '".$propertyUid."'
            and r.checkOut < '" . $datetime->format('Y-m-d') . "'
            and r.checkIn > '" . $maxPastDate->format('Y-m-d') . "'
            and r.status = '".$status->getId()."'
            order by r.checkOut desc")
                ->getResult();
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info(print_r($responseArray, true));
        }


        $this->logger->info("Ending Method before the return: " . __METHOD__);

        return $reservations;
    }

    public function getCheckOutReservation($propertyUid): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $reservations = "";
        try {
            $datetime = new DateTime();
            $status =  $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));

            $reservations = $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
                JOIN r.room a
                JOIN a.property p
            WHERE p.uid = '".$propertyUid."'
            and r.checkOut = '" . $datetime->format('Y-m-d') . "'
            and r.status = '".$status->getId()."'
            order by r.checkOut desc")
                ->getResult();

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info(print_r($responseArray, true));
        }
        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $reservations;
    }

    public function getReservationsByRoomAndDaysToCheckIn($roomId, $days)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        try {
            $now = new DateTime('today midnight');
            $maxPastDate = $now->add(new DateInterval("P".$days."D"));
            $status =  $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));

            return $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
            WHERE r.checkIn = '" . $maxPastDate->format('Y-m-d') . "'
            and r.room = $roomId 
            and r.status = ".$status->getId())
                ->getResult();
        } catch (Exception) {
            return null;
        }
    }

    public function getReservationsByRoomAndOrigin($roomId, $origin)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        try {
            $now = new DateTime('today midnight');
            $yesterdayDate = $now->sub(new DateInterval("P1D"));
            $status =  $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));

            return $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
            WHERE r.checkIn > '" . $yesterdayDate->format('Y-m-d') . "'
            and r.room = $roomId 
            and r.origin = $origin
            and r.status = ".$status->getId())
                ->getResult();

        } catch (Exception) {
            return null;
        }
    }

    public function getReservationsByRoom($roomId)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        try {
            $now = new DateTime('today midnight');
            $maxPastDate = $now->sub(new DateInterval("P".ICAL_PAST_DAYS."D"));
            $status =  $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));

            return $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
            WHERE r.checkIn > '" . $maxPastDate->format('Y-m-d') . "'
            and r.room = $roomId 
            and r.status = ".$status->getId())
                ->getResult();
        } catch (Exception) {
            return null;
        }
    }

    public function getStayOversReservations($propertyUid): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $reservations = "";
        try {
            $status =  $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));
            $reservations = $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
                JOIN r.room a
                JOIN a.property p
            WHERE p.uid = '".$propertyUid."'
            and r.checkIn < CURRENT_DATE() 
            And r.checkOut > CURRENT_DATE() 
            and r.status = '".$status->getId()."'
            order by r.checkIn asc")
                ->getResult();
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info(print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $reservations;
    }

    public function updateReservation($reservation): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

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
                'result_message' => $ex->getMessage()
            );
            $this->logger->info(print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function updateReservationDate($reservation, $checkInDate, $checkOutDate): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        $responseArray = array();
        try {
            $roomApi = new RoomApi($this->em, $this->logger);
            $isRoomAvailable = $roomApi->isRoomAvailable($reservation->getRoom()->getId(),$checkInDate, $checkOutDate,$reservation->getId());
            if($isRoomAvailable){
                $reservation->setCheckIn(new DateTime($checkInDate));
                $reservation->setCheckOut(new DateTime($checkOutDate));
                $this->em->persist($reservation);
                $this->em->flush($reservation);
            }else{
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
                'result_message' => $ex->getMessage()
            );
            $this->logger->info(print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function updateReservationRoom($reservation, $roomId): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        $responseArray = array();
        try {
            $roomApi = new RoomApi($this->em, $this->logger);
            $isRoomAvailable = $roomApi->isRoomAvailable($roomId,$reservation->getCheckIn()->format("Y-m-d"), $reservation->getCheckOut()->format("Y-m-d"),$reservation->getId());
            if($isRoomAvailable){
                $room = $roomApi->getRoom($roomId);
                $reservation->setRoom($room);
                $this->em->persist($reservation);
                $this->em->flush($reservation);
            }else{
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
                'result_message' => $ex->getMessage()
            );
            $this->logger->info(print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function createReservation($roomId,$guestName,$phoneNumber,$email,$checkInDate,$checkOutDate, $uid = null, $isImport = false, $origin = "website"): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            //get property Id
            $roomApi = new RoomApi($this->em,$this->logger);
            $propertyUid = $roomApi->getRoom($roomId)->getProperty()->getUid();
            //get guest
            $guestApi = new GuestApi($this->em, $this->logger);
            $guest = $guestApi->getGuestByPhoneNumber($phoneNumber,$propertyUid);
            if ($guest == null) {
                $this->logger->info("guest not found, creating a new guest");
                //create guest
                $response = $guestApi->createGuest($guestName, $phoneNumber, $email, $propertyUid);
                if ($response[0]['result_code'] != 0) {
                    $this->logger->info(print_r($response, true));
                    return $response;
                } else {
                    $guest = $response[0]['guest'];
                }
            }
            //get room
            $roomApi = new RoomApi($this->em, $this->logger);
            $blockRoomApi = new BlockedRoomApi($this->em, $this->logger);
            $room = $roomApi->getRoom($roomId);

            $paid = 0;
            //check if room is available
            $isRoomAvailable = $roomApi->isRoomAvailable($room->getId(), $checkInDate, $checkOutDate);

            if (!$isRoomAvailable) {
                $responseArray[] = array(
                    'result_code' => 1,
                    'result_message' => 'Room not available for selected dates'
                );
                return $responseArray;
            }

            $reservation = new Reservations();
            $reservation->setRoom($room);
            $reservation->setAdditionalInfo($phoneNumber);
            $reservation->setCheckIn(new DateTime($checkInDate));
            $reservation->setCheckOut(new DateTime($checkOutDate));
            $reservation->setGuest($guest);

            $reservation->setOrigin("website");
            $reservation->setReceivedOn(new DateTime());
            $reservation->setUpdatedOn(new DateTime());
            $reservation->setCheckedInTime(NULL);
            $reservation->setOriginUrl($origin);

            if($isImport){
                $status =  $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));
            }else{
                $status =  $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'pending'));
            }

            $reservation->setStatus($status);

            if($uid == null){
                $reservation->setUid(uniqid()  . "@" . SERVER_NAME);
            }else{
                $reservation->setUid($uid);
            }

            $this->em->persist($reservation);
            $this->em->flush($reservation);

            //add Short stay 3 hour add-on if check out is same day
            $totalDays = intval($reservation->getCheckIn()->diff($reservation->getCheckOut())->format('%a'));
            $this->logger->info("Date diff is $totalDays");
            if($totalDays === 0){
                $this->logger->info("Short Stay");
                $addOnsApi = new AddOnsApi($this->em, $this->logger);
                $addon = $addOnsApi->getAddOn("Short Stay - 3 Hours", $propertyUid);
                $addOnsApi->addAdOnToReservation($reservation->getId(), $addon->getId(), 1);
            }else{
                $this->logger->info("overnight Stay");
            }

            //block connected Room
            $blockRoomApi->blockRoom($room->getLinkedRoom(), "Connected Room Booked", $checkInDate, $checkOutDate);

            //Send SMS
            if(!$isImport){
                if (str_starts_with($reservation->getGuest()->getPhoneNumber(), '0') || str_starts_with($reservation->getGuest()->getPhoneNumber(), '+27')) {
                    $SMSHelper = new SMSHelper($this->logger);
                    $message = "Hi " . $guest->getName() . ", Thank you for your reservation. Please make payment to confirm the reservation. View your invoice http://".SERVER_NAME."/invoice.html?reservation=" . $reservation->getId();
                    $SMSHelper->sendMessage($guest->getPhoneNumber(), $message);
                    $responseArray[] = array(
                        'result_code' => 0,
                        'result_message' => "Successfully created reservation",
                        'reservation_id' => $reservation->getId()
                    );
                }else{
                    if (!empty($reservation->getGuest()->getEmail())) {
                        $emailBody = file_get_contents(__DIR__ . '/../email_template/thank_you_for_payment.html');
                        $emailBody = str_replace("guest_name",$reservation->getGuest()->getName(),$emailBody);
                        $emailBody = str_replace("check_in",$reservation->getCheckIn()->format("d M Y"),$emailBody);
                        $emailBody = str_replace("check_out",$reservation->getCheckOut()->format("d M Y"),$emailBody);
                        $emailBody = str_replace("server_name",SERVER_NAME, $emailBody);
                        $emailBody = str_replace("reservation_id",$reservation->getId(),$emailBody);
                        mail($reservation->getGuest()->getEmail(), 'Thank you for payment', $emailBody);
                        $responseArray[] = array(
                            'result_code' => 0,
                            'result_message' => "Successfully created reservation but email or sms not sent",
                            'reservation_id' => $reservation->getId()
                        );
                    }else{
                        $responseArray[] = array(
                            'result_code' => 0,
                            'result_message' => "Successfully created reservation",
                            'reservation_id' => $reservation->getId()
                        );
                    }
                }
            }

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_code' => 1,
                'result_message' => $ex->getMessage()
            );
            $this->logger->info(print_r($responseArray, true));
        }


        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function isEligibleForCheckIn($reservation): bool
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $isEligible = true;
        if (strcasecmp($reservation->getGuest()->getIdImage(), "unverified.png") == 0 && strcasecmp($reservation->getOrigin(), "website") == 0) {
            $isEligible = false;
        }

        if (strcasecmp($reservation->getGuest()->getPhoneNumber(), "not provided") == 0) {
            $isEligible = false;
        }
        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $isEligible;
    }

    public function getAmountDue($reservation): bool
    {
        $this->logger->info("Starting Method: " . __METHOD__);

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
        $this->logger->info("Total Add Ons: " . $totalPriceForAllAdOns);
        $this->logger->info("Room Price: " . $roomPrice);
        $this->logger->info("Total Days: " . $totalDays);
        $this->logger->info("Total Price: " . $totalPrice);
        $this->logger->info("Total Paid: " . $totalPayment);
        $this->logger->info("due: " . $due);

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $due;
    }

}