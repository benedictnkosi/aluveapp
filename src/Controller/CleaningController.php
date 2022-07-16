<?php

namespace App\Controller;

use App\Service\CleaningApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class CleaningController extends AbstractController
{
    /**
     * @Route("api/cleanings/{roomId}")
     */
    public function getCleanings($roomId, LoggerInterface $logger, EntityManagerInterface $entityManager, CleaningApi $cleaningApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $cleaningApi->getCleaningsByRoom($roomId);
        return new Response(
            $response
        );
    }

    /**
     * @Route("api/cleaning/{reservationId}/cleaner/{cleanerId}")
     */
    public function addPayment($reservationId, $cleanerId, LoggerInterface $logger, EntityManagerInterface $entityManager, CleaningApi $cleaningApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $cleaningApi->addCleaningToReservation($reservationId,$cleanerId);
        $logger->info("Notification " .$response);
        return  $this->json($response);
    }

}