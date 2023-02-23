<?php

namespace App\Controller;

use App\Helpers\FormatHtml\BookingPageAvailableRoomsHTML;
use App\Helpers\FormatHtml\ConfigurationRoomsHTML;
use App\Helpers\FormatHtml\RoomImagesHTML;
use App\Helpers\FormatHtml\RoomsPageHTML;
use App\Service\AddOnsApi;
use App\Service\PropertyApi;
use App\Service\RoomApi;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerBuilder;
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
     * @Route("/public/rooms/{roomId}", defaults={"roomId": "all", "propertyUid": "none"})
     */
    public function getRooms($roomId, LoggerInterface $logger, Request $request,RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->getRooms($roomId, $request);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }


    /**
     * @Route("/public/allroomsjson")
     */
    public function getAllRooms( LoggerInterface $logger, Request $request,RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->getRooms("all", $request);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/public/availablerooms/{checkInDate}/{checkOutDate}/{propertyUid}", defaults={"propertyUid": 0})
     */
    public function getAvailableRooms($checkInDate, $checkOutDate,$propertyUid, Request $request, LoggerInterface $logger,EntityManagerInterface $entityManager, RoomApi $roomApi, PropertyApi $propertyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $rooms = $roomApi->getAvailableRooms($checkInDate, $checkOutDate, $request, $propertyUid);
        $availableRoomsDropDownHTML = new BookingPageAvailableRoomsHTML($entityManager, $logger);
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
     * @Route("/public/allrooms/")
     */
    public function getRoomsHtml(LoggerInterface $logger,Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi, PropertyApi $propertyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $rooms = $roomApi->getRoomsEntities(0, $request);
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
    public function getConfigurationRooms( LoggerInterface $logger,Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $rooms = $roomApi->getRoomsEntities();
        $roomsPageHTML = new ConfigurationRoomsHTML($entityManager, $logger);
        $html = $roomsPageHTML->formatLeftDivRoomsHtml($rooms);
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
    public function getComboListRooms( LoggerInterface $logger,Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $rooms = $roomApi->getRoomsEntities();
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
     * @Route("/api/combolistroombedsizesjson")
     */
    public function getComboListRoomBedSizesJson(LoggerInterface $logger,Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $bedSizes = $roomApi->getRoomBedSizesJson();
        return new JsonResponse(json_encode($bedSizes) , 200, array());
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
     * @Route("/public/roomspage/{checkin}/{checkout}")
     */
    public function getFilteredRoomsHtml($checkin, $checkout, LoggerInterface $logger,Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi, PropertyApi $propertyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        $rooms = $roomApi->getAvailableRooms($checkin, $checkout, $request);
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
     * @Route("/admin_api/createroom")
     */
    public function updateCreateRoom(Request $request, LoggerInterface $logger,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('post')) {
            return new JsonResponse("Internal server error" , 500, array());
        }
        $response = $roomApi->updateCreateRoom($request->get('room_id'), $request->get('room_name'), $request->get('room_price'), $request->get('room_sleeps'),
            $request->get('room_status'), $request->get('linked_room'), $request->get('room_size'), $request->get('bed'), $request->get('stairs'),$request->get('tv')
            , str_replace("###", "/", $request->get('description')));

        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/public/roomslide/{roomId}")
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

    /**
     * @Route("api/json/room/{id}")
     */
    public function getAddOnJson( $id, LoggerInterface $logger, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $room = $roomApi->getRoom($id);

        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($room, 'json');

        $logger->info($jsonContent);
        return new JsonResponse($jsonContent , 200, array(), true);
    }
}