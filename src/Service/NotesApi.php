<?php

namespace App\Service;

use App\Entity\ReservationNotes;
use App\Entity\Reservations;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

class NotesApi
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

    public function addNote($resId, $note)
    {
        $this->logger->info("Starting Method: " . __METHOD__ );
        $responseArray = array();
        try{
            $reservation = $this->em->getRepository(Reservations::class)->findOneBy(array('id'=>$resId));
            $reservationNotes = new ReservationNotes();
            $now = new DateTime('today midnight');

            $reservationNotes->setReservation($reservation);
            $reservationNotes->setNote($note);
            $reservationNotes->setDate($now);
            $this->em->persist($reservationNotes);
            $this->em->flush($reservationNotes);
            $responseArray[] = array(
                'result_code' => 0,
                'result_message' => 'Successfully added note'
            );
            $this->logger->info("no errors adding note for reservation $resId. note $note");
        }catch(Exception $ex){
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code'=> 1
            );
            $this->logger->info("failed to get payments " . print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__ );
        return $responseArray;
    }

    public function getReservationNotes($resId)
    {
        $this->logger->info("Starting Method: " . __METHOD__ );
        $responseArray = array();
        try{
            $notes = $this->em->getRepository(ReservationNotes::class)->findBy(array('reservation'=>$resId));
            $this->logger->info("no errors finding notes for reservation $resId. notes count " . count($notes));
            return $notes;
        }catch(Exception $ex){
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code'=> 1
            );
            $this->logger->info("failed to get notes " . print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__ );
        return $responseArray;
    }
}