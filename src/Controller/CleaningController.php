<?php

namespace App\Controller;

use App\Service\CleaningApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CleaningController extends AbstractController
{
    /**
     * @Route("api/cleanings/{roomId}")
     */
    public function getCleanings($roomId, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, CleaningApi $cleaningApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $html = $cleaningApi->getCleaningsByRoom($roomId);
        $response = array(
            'html' => $html,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/cleaning/{reservationId}/cleaner/{cleanerId}")
     */
    public function addCleanerToReservation($reservationId, $cleanerId, LoggerInterface $logger,Request $request, EntityManagerInterface $entityManager, CleaningApi $cleaningApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $cleaningApi->addCleaningToReservation($reservationId,$cleanerId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

}