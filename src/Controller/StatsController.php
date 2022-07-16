<?php

namespace App\Controller;
use App\Service\StatsApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends AbstractController
{
    /**
     *  @Route("/api/stats/getreservationcount/{type}/{day}", name="getreservationcount", defaults={"type": "checkin", "day": "today"})
     */
    public function getReservationCount($type, $day, LoggerInterface $logger, StatsApi $statsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__ );
        $count = $statsApi->getReservationCount($type, $day);
        return  $this->json($count);
    }

    /**
     *  @Route("/api/stats/getstayovercount/{day}", name="getstayovercount", defaults={"day": "today"})
     */
    public function getStayOverCount($day, LoggerInterface $logger, StatsApi $statsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__ );
        $count = $statsApi->getStayOverCount($day);
        return  $this->json($count);
    }
}