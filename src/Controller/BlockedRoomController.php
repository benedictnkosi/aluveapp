<?php

namespace App\Controller;

use App\Helpers\FormatHtml\BlockedRoomsHTML;
use App\Service\BlockedRoomApi;
use App\Service\RoomApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlockedRoomController extends AbstractController
{

    /**
     * @Route("/api/blockroom/{room}/{date}/{comments}")
     */
    public function blockRoom($room, $date,$comments , LoggerInterface $logger, EntityManagerInterface $entityManager, BlockedRoomApi $blockedRoomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        $response = $blockedRoomApi->blockRoom($room,  urldecode($date), urldecode($comments));
        return $this->json($response);
    }

    /**
     * @Route("/api/blockedroom/get")
     */
    public function getBlockedRooms(LoggerInterface $logger, EntityManagerInterface $entityManager, BlockedRoomApi $blockedRoomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $blockedRooms = $blockedRoomApi->getBlockedRooms();
        $blockedRoomsHTML = new BlockedRoomsHTML($entityManager, $logger);
        $formattedHtml = $blockedRoomsHTML->formatHtml($blockedRooms);
        return new Response(
            $formattedHtml
        );
    }

    /**
     * @Route("/api/blockedroom/delete/{id}")
     */
    public function deleteBlockedRooms($id, LoggerInterface $logger, EntityManagerInterface $entityManager, BlockedRoomApi $blockedRoomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $blockedRoomApi->deleteBlockedRoom($id);
        return $this->json($response);
    }
}