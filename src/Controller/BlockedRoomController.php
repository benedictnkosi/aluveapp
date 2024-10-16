<?php

namespace App\Controller;

use App\Helpers\FormatHtml\BlockedRoomsHTML;
use App\Service\BlockedRoomApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class BlockedRoomController extends AbstractController
{

    /**
     * @Route("/api/blockroom/{room}/{fromDate}/{toDate}/{comments}")
     */
    public function blockRoom($room, $fromDate, $toDate, $comments , LoggerInterface $logger,Request $request,EntityManagerInterface $entityManager, BlockedRoomApi $blockedRoomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        $response = $blockedRoomApi->blockRoom($room,  $fromDate, $toDate, urldecode($comments));
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/blockedroom/get")
     */
    public function getBlockedRooms( LoggerInterface $logger, Request $request, EntityManagerInterface $entityManager, BlockedRoomApi $blockedRoomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $blockedRooms = $blockedRoomApi->getBlockedRoomsByProperty();
        $blockedRoomsHTML = new BlockedRoomsHTML($entityManager, $logger);
        $formattedHtml = $blockedRoomsHTML->formatHtml($blockedRooms);
        $response = array(
            'html' => $formattedHtml,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/blockedroom/delete/{id}")
     */
    public function deleteBlockedRooms($id, LoggerInterface $logger, Request $request, EntityManagerInterface $entityManager, BlockedRoomApi $blockedRoomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $blockedRoomApi->deleteBlockedRoom($id);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }
}