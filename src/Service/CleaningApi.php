<?php

namespace App\Service;

use App\Entity\Cleaning;
use App\Entity\Employee;
use App\Entity\Reservations;
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
        if(session_id() === ''){
            $logger->info("Session id is empty");
            session_start();
        }
    }

    public function addCleaningToReservation($resId, $employeeId): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $reservation = $this->em->getRepository(Reservations::class)->findOneBy(array('id' => $resId));
            $employee = $this->em->getRepository(Employee::class)->findOneBy(array('id' => $employeeId));

            $cleaning = new Cleaning();
            $now = new DateTime('today midnight');

            $cleaning->setReservation($reservation);
            $cleaning->setCleaner($employee);
            $cleaning->setDate($now);

            $this->em->persist($cleaning);
            $this->em->flush($cleaning);
            $responseArray[] = array(
                'result_code' => 0,
                'result_message' => 'Successfully added cleaning to reservation'
            );
            $this->logger->info("no errors adding cleaning for reservation $resId. cleaner $employee->getId()");
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info("Error " . print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function isRoomCleanedForCheckOut($resId)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
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
            }else{
                $responseArray[] = array(
                    'cleaned' => false
                );
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info("Error " . print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function getReservationCleanings($resId)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            return $this->em->getRepository(Cleaning::class)->findBy(array('reservation' => $resId));
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info("Error " . print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function getReservationLastCleaning($resId)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $cleanings = $this->em->getRepository(Cleaning::class)->findBy(array('reservation' => $resId),
                array('date' => 'ASC'),
            1);
            return $cleanings;
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info("Error " . print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function getCleaningsByRoom($roomId)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $htmlResponse = "";
            $reservations  =  $this->em->getRepository(Reservations::class)->findBy(array('room' => $roomId));
            $cleaningsFound = false;
            foreach($reservations  as $reservation){
                $cleanings = $this->em->getRepository(Cleaning::class)->findBy(
                    array('reservation' => $reservation->getId()),
                array('date' => 'desc'),
                    100
                );
                foreach($cleanings  as $cleaning){
                    $date = $cleaning->getDate()->format("Y-m-d");
                    $room = $reservation->getRoom()->getName();
                    $cleanerName = $cleaning->getCleaner()->getName();
                    $htmlResponse .= '<h5 class="em1-top-padding">'.$date.' -  ' . $cleanerName .' cleaned '. $room .'</h5>';
                    $cleaningsFound = true;
                }
            }

            if(!$cleaningsFound){
                return "<h5>No cleanings found for this room</h5>";
            }

        } catch (Exception $ex) {
            $htmlResponse = "Failed to get Cleaning for room";
            $this->logger->info("Error " . print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $htmlResponse;
    }


}