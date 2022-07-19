<?php

namespace App\Controller;
use App\Service\StatsApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class StatsController extends AbstractController
{
    /**
     *  @Route("/api/stats/getreservationcount/{type}/{day}/{propertyUid}", name="getreservationcount", defaults={"type": "checkin", "day": "today"})
     */
    public function getReservationCount($type, $day,$propertyUid, Request $request,LoggerInterface $logger, StatsApi $statsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__ );
        $response = $statsApi->getReservationCount($type, $day, $propertyUid);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     *  @Route("/api/stats/getstayovercount/{day}/{propertyUid}", name="getstayovercount", defaults={"day": "today"})
     */
    public function getStayOverCount($day, $propertyUid, Request $request, LoggerInterface $logger, StatsApi $statsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__ );
        $response = $statsApi->getStayOverCount($day, $propertyUid);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }
}