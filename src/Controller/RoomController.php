<?php

namespace App\Controller;

use App\Helpers\FormatHtml\AvailableRoomsDropDownHTML;
use App\Helpers\FormatHtml\ConfigurationRoomsHTML;
use App\Helpers\FormatHtml\RoomsPageHTML;
use App\Service\RoomApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class RoomController extends AbstractController
{
    /**
     * @Route("/api/rooms/{roomId}", name="rooms", defaults={"roomId": 0})
     */
    public function getRooms($roomId, LoggerInterface $logger, Request $request,RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->getRooms($roomId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/rooms/{checkInDate}/{checkOutDate}")
     */
    public function getAvailableRooms($checkInDate, $checkOutDate,Request $request, LoggerInterface $logger,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $rooms = $roomApi->getAvailableRooms($checkInDate, $checkOutDate);
        $availableRoomsDropDownHTML = new AvailableRoomsDropDownHTML($entityManager, $logger);
        $response = $availableRoomsDropDownHTML->formatHtml($rooms);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;

    }

    /**
     * @Route("/api/allrooms")
     */
    public function getRoomsHtml(LoggerInterface $logger,Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $rooms = $roomApi->getRoomsEntities();
        $roomsPageHTML = new RoomsPageHTML($entityManager, $logger);
        $response = $roomsPageHTML->formatHtml($rooms, $roomApi);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/configurationrooms")
     */
    public function getConfigurationRooms(LoggerInterface $logger,Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $rooms = $roomApi->getRoomsEntities();
        $roomsPageHTML = new ConfigurationRoomsHTML($entityManager, $logger);
        $response = $roomsPageHTML->formatRightDivRoomsHtml($rooms);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/combolistrooms")
     */
    public function getComboListRooms(LoggerInterface $logger,Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $rooms = $roomApi->getRoomsEntities();
        $roomsPageHTML = new ConfigurationRoomsHTML($entityManager, $logger);
        $response = $roomsPageHTML->formatComboListHtml($rooms, true);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/combolistroomstatuses")
     */
    public function getComboListRoomStatuses(LoggerInterface $logger,Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $statuses = $roomApi->getRoomStatuses();
        $roomsPageHTML = new ConfigurationRoomsHTML($entityManager, $logger);
        $response = $roomsPageHTML->formatComboListHtml($statuses);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/combolistroombedsizes")
     */
    public function getComboListRoomBedSizes(LoggerInterface $logger,Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $bedSizes = $roomApi->getRoomBedSizes();
        $roomsPageHTML = new ConfigurationRoomsHTML($entityManager, $logger);
        $response = $roomsPageHTML->formatComboListHtml($bedSizes);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/roomspage/{checkin}/{checkout}")
     */
    public function getFilteredRoomsHtml($checkin, $checkout, LoggerInterface $logger,Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $rooms = $roomApi->getAvailableRooms($checkin, $checkout);
        $roomsPageHTML = new RoomsPageHTML($entityManager, $logger);
        $response = $roomsPageHTML->formatHtml($rooms);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/createroom/{id}/{name}/{price}/{sleeps}/{status}/{linkedRoom}/{size}/{bed}/{stairs}/{description}")
     */
    public function updateCreateRoom($id, $name, $price, $sleeps, $status, $linkedRoom, $size, $bed, $stairs, $description,Request $request, LoggerInterface $logger,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->updateCreateRoom($id, $name, $price, $sleeps, $status, $linkedRoom, $size, $bed, $stairs, $description);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }
}