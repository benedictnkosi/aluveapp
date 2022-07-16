<?php

namespace App\Service;


use App\Entity\Reservations;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class StatsApi
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

    public function getReservationCount($type, $day): array
    {
        $datetime = new DateTime();
        if (strcasecmp($day, "tomorrow") == 0) {
            $datetime->add(new DateInterval('P1D'));
        }
        $this->logger->info("Starting Method: " . __METHOD__ );
        $checkins = $this->em->getRepository(Reservations::class)->findBy(array($type => $datetime,
            'status'=>'confirmed'));
        $responseArray = array();
        $responseArray[] = array(
            'count' => count($checkins),
            'result_code'=> 0
        );
        $this->logger->info("Ending Method before the return: " . __METHOD__ );
        return $responseArray;
    }

    public function getStayOverCount($day): array
    {
        $datetime = new DateTime();
        if (strcasecmp($day, "tomorrow") == 0) {
            $datetime->add(new DateInterval('P1D'));
        }
        $this->logger->info("Starting Method: " . __METHOD__ );
        $stayOvers = $this->em
            ->createQuery("SELECT r FROM App\Entity\Reservations r 
            WHERE r.checkIn < CURRENT_DATE() 
            And r.checkOut > CURRENT_DATE()
            and r.status = 'confirmed'")
            ->getResult();

        $responseArray = array();
        $responseArray[] = array(
            'count' => count($stayOvers),
            'result_code'=> 0
        );
        $this->logger->info("Ending Method before the return: " . __METHOD__ );
        return $responseArray;
    }
}