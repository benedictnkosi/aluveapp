<?php

namespace App\Service;

use App\Entity\Cleaning;
use App\Entity\Employee;
use App\Entity\Reservations;
use App\Entity\ReservationStatus;
use App\Entity\Rooms;
use App\Entity\RoomStatus;
use App\Helpers\DatabaseHelper;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

class CleaningApi
{

    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        if (session_id() === '') {
            $logger->info("Session id is empty" . __METHOD__);
            session_start();
        }
    }

    public function addCleaningToReservation($resId, $cleanerId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $reservation = $this->em->getRepository(Reservations::class)->findOneBy(array('id' => $resId));
            $employee = $this->em->getRepository(Employee::class)->findOneBy(array('id' => $cleanerId));

            $cleaning = new Cleaning();
            $now = new DateTime('today');

            $cleaning->setReservation($reservation);

            $cleaning->setReservation($reservation);
            $cleaning->setCleaner($employee);
            $cleaning->setDate($now);

            $this->em->persist($cleaning);
            $this->em->flush($cleaning);

            //open room if its a short stay
            if(strcmp($reservation->getCheckOut()->format("Y-m-d"), $now->format("Y-m-d") == 0)){
                $status =  $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => "Opened"));
                $reservation->setStatus($status);
                $this->em->persist($reservation);
                $this->em->flush($reservation);
            }
            $responseArray[] = array(
                'result_code' => 0,
                'result_message' => 'Successfully added cleaning to reservation'
            );

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("Error " . print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function isRoomCleanedForCheckOut($resId)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $reservation = $this->em->getRepository(Reservations::class)->findOneBy(array('id' => $resId));

            $cleanings = $this->em
                ->createQuery("SELECT c FROM App\Entity\Cleaning c 
            WHERE c.date >= " . $reservation->getCheckOut()->format("Y-m-d") . " 
            And c.reservation = " . $reservation->getId())
                ->getResult();

            if (count($cleanings) > 0) {
                foreach ($cleanings as $cleaning) {
                    $responseArray[] = array(
                        'cleaned' => true,
                        'cleaned_by' => $cleaning->getCleaner()->getName()
                    );
                }
            } else {
                $responseArray[] = array(
                    'cleaned' => false
                );
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("Error " . print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function getReservationCleanings($resId)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            return $this->em->getRepository(Cleaning::class)->findBy(array('reservation' => $resId));
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("Error " . print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function isCleaningRequiredToday($reservation): bool
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $cleaning = $this->em->getRepository(Cleaning::class)->findOneBy(array('reservation' => $reservation->getId()),
            array('date' => 'DESC'));


        $lastCleanDate = $reservation->getCheckIn();
        if ($cleaning !== null) {
            $lastCleanDate = $cleaning->getDate();
        }

        $now = new DateTime();
        $totalDaysSinceCleaning = intval($now->diff($lastCleanDate)->format('%a'));
        $this->logger->debug("days since last cleaning is " . $totalDaysSinceCleaning);
        if ($totalDaysSinceCleaning > 1) {
            return true;
        } else {
            return false;
        }
    }

    public function getCleaningsByRoom($roomId)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $htmlResponse = "";
            $reservations = $this->em->getRepository(Reservations::class)->findBy(array('room' => $roomId),
                array('checkOut' => 'desc'));
            $cleaningsFound = false;
            foreach ($reservations as $reservation) {
                $cleanings = $this->em->getRepository(Cleaning::class)->findBy(
                    array('reservation' => $reservation->getId()),
                    array('date' => 'desc'),
                    100
                );
                foreach ($cleanings as $cleaning) {
                    $date = $cleaning->getDate()->format("Y-m-d");
                    $room = $reservation->getRoom()->getName();
                    $cleanerName = $cleaning->getCleaner()->getName();
                    $htmlResponse .= '<h5 class="em1-top-padding">' . $date . ' -  ' . $cleanerName . ' cleaned ' . $room . '</h5>';
                    $cleaningsFound = true;
                }
            }

            if (!$cleaningsFound) {
                return "<h5>No cleanings found for this room</h5>";
            }

        } catch (Exception $ex) {
            $htmlResponse = "Failed to get Cleaning for room";
            $this->logger->error("Error " . $ex->getMessage());
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $htmlResponse;
    }

    public function getOutstandingCleaningsForToday()
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $htmlResponse = "<tr><th>Room Name</th><th>Reason</th></tr>";

            //get check-outs without cleaning
            $now = new DateTime('today');
            $twoDaysAgo = date("Y-m-d", strtotime("-2 day"));
            $oneDaysAgo = date("Y-m-d", strtotime("-1 day"));

            $this->logger->debug("date 2 days " . $twoDaysAgo);

            $checkOutsWithoutCleaningSQL = "SELECT `reservations`.id, name FROM `reservations`, rooms
WHERE reservations.room_id = rooms.id
and check_out = '" . $now->format("Y-m-d") . "'
and `reservations`.status =1
and `reservations`.id not IN (Select reservation_id from cleaning where date = '" . $now->format("Y-m-d") . "');";

            $this->logger->debug($checkOutsWithoutCleaningSQL);

            $stayOversWithoutCleaningSQL = "SELECT `reservations` .id, name FROM `reservations` , rooms
WHERE `reservations`.`room_id` = rooms.id
and check_out > '" . $now->format("Y-m-d") . "'
and check_in < '" . $oneDaysAgo . "'
and `reservations`.status =1
and `reservations`.id not IN (Select reservation_id from cleaning where date > '" . $twoDaysAgo . "');
";
            $this->logger->debug($stayOversWithoutCleaningSQL);

            $databaseHelper = new DatabaseHelper($this->logger);
            $result = $databaseHelper->queryDatabase($checkOutsWithoutCleaningSQL);

            if ($result) {
                while ($results = $result->fetch_assoc()) {
                    $htmlResponse .= "<tr><td>".$results["name"]."</td><td>Guest checked out</td></tr>";
                }
            }

            $result = $databaseHelper->queryDatabase($stayOversWithoutCleaningSQL);

            if ($result) {
                while ($results = $result->fetch_assoc()) {
                    $htmlResponse .= "<tr><td>".$results["name"]."</td><td>Not cleaned in 2 days</td></tr>";
                }
            }
        } catch (Exception $ex) {
            $this->logger->error("Error " . $ex->getMessage());
        }

        return $htmlResponse;
    }


}