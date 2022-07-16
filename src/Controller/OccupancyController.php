<?php

namespace App\Controller;

use App\Service\NotesApi;
use App\Service\OccupancyApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class OccupancyController extends AbstractController
{
    /**
     * @Route("api/occupancy/{days}")
     */
    public function getOccupancy($days, LoggerInterface $logger, EntityManagerInterface $entityManager, OccupancyApi $occupancyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $occupancyApi->getOccupancy($days);
        return  $this->json($response);
    }


    /**
     * @Route("api/occupancy/perroom/{days}")
     */
    public function getOccupancyPerRoom($days, LoggerInterface $logger, EntityManagerInterface $entityManager, OccupancyApi $occupancyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $occupancyApi->getOccupancyPerRoom($days);
        return new Response(
            $response
        );
    }

}