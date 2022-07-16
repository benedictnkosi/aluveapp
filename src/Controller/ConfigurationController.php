<?php

namespace App\Controller;

use App\Service\RoomApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ConfigurationController extends AbstractController
{
    /**
     * @Route("api/configuration/rooms")
     */
    public function getConfigRooms(LoggerInterface $logger, EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->getRooms(0);
        return $this->json($response);
    }

    /**
     * @Route("api/configuration/roomstatus")
     */
    public function getRoomStatuses(LoggerInterface $logger, EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->getRoomStatuses();
        return $this->json($response);
    }

    /**
     * @Route("api/configuration/roombedsizes")
     */
    public function getRoomBedSizes(LoggerInterface $logger, EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->getRoomBedSizes();
        return $this->json($response);
    }

    /**
     * @Route("api/configuration/removeimage/{imageId}")
     */
    public function removeImage($imageId, LoggerInterface $logger, EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->removeImage($imageId);
        return $this->json($response);
    }

    /**
     * @Route("api/configuration/markdefault/{imageId}")
     */
    public function markDefault($imageId, LoggerInterface $logger, EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->markDefault($imageId);
        return $this->json($response);
    }



}