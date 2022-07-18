<?php

namespace App\Controller;

use App\Helpers\FormatHtml\AvailableRoomsDropDownHTML;
use App\Helpers\FormatHtml\ConfigurationRoomsHTML;
use App\Helpers\FormatHtml\RoomImagesHTML;
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
     * @Route("/api/rooms/addimage/{roomId}/{fileName}")
     */
    public function addImageToRoom($roomId,$fileName, LoggerInterface $logger, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->addImageToRoom($fileName, $roomId);
        return new JsonResponse($response);
    }

    /**
     * @Route("/api/rooms/{roomId}", name="rooms", defaults={"roomId": "all"})
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
        $rooms = $roomApi->getAvailableRooms($checkInDate, $checkOutDate, $request);
        $availableRoomsDropDownHTML = new AvailableRoomsDropDownHTML($entityManager, $logger);
        $html = $availableRoomsDropDownHTML->formatHtml($rooms);
        $response = array(
            'html' => $html,
        );
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
        $rooms = $roomApi->getRoomsEntities($request);
        $roomsPageHTML = new RoomsPageHTML($entityManager, $logger);
        $html = $roomsPageHTML->formatHtml($rooms, $roomApi);
        $response = array(
            'html' => $html,
        );
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
        $rooms = $roomApi->getRoomsEntities($request);
        $roomsPageHTML = new ConfigurationRoomsHTML($entityManager, $logger);
        $html = $roomsPageHTML->formatRightDivRoomsHtml($rooms);
        $response = array(
            'html' => $html,
        );
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
        $rooms = $roomApi->getRoomsEntities($request);
        $roomsPageHTML = new ConfigurationRoomsHTML($entityManager, $logger);
        $html = $roomsPageHTML->formatComboListHtml($rooms, true);
        $response = array(
            'html' => $html,
        );
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
        $html = $roomsPageHTML->formatComboListHtml($statuses);
        $response = array(
            'html' => $html,
        );
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
        $html = $roomsPageHTML->formatComboListHtml($bedSizes);
        $response = array(
            'html' => $html,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/combolistroomtvs")
     */
    public function getComboListRoomTvs(LoggerInterface $logger,Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $bedSizes = $roomApi->getRoomTvs();
        $roomsPageHTML = new ConfigurationRoomsHTML($entityManager, $logger);
        $html = $roomsPageHTML->formatComboListHtml($bedSizes);
        $response = array(
            'html' => $html,
        );
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
        $rooms = $roomApi->getAvailableRooms($checkin, $checkout, $request);
        $roomsPageHTML = new RoomsPageHTML($entityManager, $logger);
        $html = $roomsPageHTML->formatHtml($rooms, $request);
        $response = array(
            'html' => $html,
        );
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

    /**
     * @Route("/api/roomslide/{roomId}")
     */
    public function getRoomSlide($roomId,Request $request, LoggerInterface $logger,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $roomImages = $roomApi->getRoomImages($roomId);
        $roomImagesHtml = new RoomImagesHTML($entityManager, $logger);
        $imagesHtml = $roomImagesHtml->formatHtml($roomImages);
        $response = array(
            'html' => $imagesHtml,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }
}