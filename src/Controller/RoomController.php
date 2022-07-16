<?php

namespace App\Controller;

use App\Helpers\FormatHtml\AvailableRoomsDropDownHTML;
use App\Helpers\FormatHtml\ConfigurationRoomsHTML;
use App\Helpers\FormatHtml\RoomsPageHTML;
use App\Service\LookupApi;
use App\Service\RoomApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoomController extends AbstractController
{
    /**
     * @Route("/api/rooms/{roomId}", name="rooms", defaults={"roomId": 0})
     */
    public function getRooms($roomId, LoggerInterface $logger, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $rooms = $roomApi->getRooms($roomId);
        return $this->json($rooms);
    }

    /**
     * @Route("/api/rooms/{checkInDate}/{checkOutDate}")
     */
    public function getAvailableRooms($checkInDate, $checkOutDate, LoggerInterface $logger,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $rooms = $roomApi->getAvailableRooms($checkInDate, $checkOutDate);
        $availableRoomsDropDownHTML = new AvailableRoomsDropDownHTML($entityManager, $logger);
        $formattedHtml = $availableRoomsDropDownHTML->formatHtml($rooms);
        return new Response(
            $formattedHtml
        );

    }

    /**
     * @Route("/api/allrooms")
     */
    public function getRoomsHtml(LoggerInterface $logger,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $rooms = $roomApi->getRoomsEntities();
        $roomsPageHTML = new RoomsPageHTML($entityManager, $logger);
        $formattedHtml = $roomsPageHTML->formatHtml($rooms, $roomApi);
        return new Response(
            $formattedHtml
        );
    }

    /**
     * @Route("/api/configurationrooms")
     */
    public function getConfigurationRooms(LoggerInterface $logger,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $rooms = $roomApi->getRoomsEntities();
        $roomsPageHTML = new ConfigurationRoomsHTML($entityManager, $logger);
        $formattedHtml = $roomsPageHTML->formatRightDivRoomsHtml($rooms);
        return new Response(
            $formattedHtml
        );
    }

    /**
     * @Route("/api/combolistrooms")
     */
    public function getComboListRooms(LoggerInterface $logger,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $rooms = $roomApi->getRoomsEntities();
        $roomsPageHTML = new ConfigurationRoomsHTML($entityManager, $logger);
        $formattedHtml = $roomsPageHTML->formatComboListHtml($rooms, true);
        return new Response(
            $formattedHtml
        );
    }

    /**
     * @Route("/api/combolistroomstatuses")
     */
    public function getComboListRoomStatuses(LoggerInterface $logger,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $statuses = $roomApi->getRoomStatuses();
        $roomsPageHTML = new ConfigurationRoomsHTML($entityManager, $logger);
        $formattedHtml = $roomsPageHTML->formatComboListHtml($statuses);
        return new Response(
            $formattedHtml
        );
    }

    /**
     * @Route("/api/combolistroombedsizes")
     */
    public function getComboListRoomBedSizes(LoggerInterface $logger,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $bedSizes = $roomApi->getRoomBedSizes();
        $roomsPageHTML = new ConfigurationRoomsHTML($entityManager, $logger);
        $formattedHtml = $roomsPageHTML->formatComboListHtml($bedSizes);
        return new Response(
            $formattedHtml
        );
    }

    /**
     * @Route("/api/roomspage/{checkin}/{checkout}")
     */
    public function getFilteredRoomsHtml($checkin, $checkout, LoggerInterface $logger,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $rooms = $roomApi->getAvailableRooms($checkin, $checkout);
        $roomsPageHTML = new RoomsPageHTML($entityManager, $logger);
        $formattedHtml = $roomsPageHTML->formatHtml($rooms);
        return new Response(
            $formattedHtml
        );
    }

    /**
     * @Route("/api/createroom/{id}/{name}/{price}/{sleeps}/{status}/{linkedRoom}/{size}/{bed}/{stairs}/{description}")
     */
    public function updateCreateRoom($id, $name, $price, $sleeps, $status, $linkedRoom, $size, $bed, $stairs, $description, LoggerInterface $logger,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->updateCreateRoom($id, $name, $price, $sleeps, $status, $linkedRoom, $size, $bed, $stairs, $description);
        return $this->json($response);
    }
}