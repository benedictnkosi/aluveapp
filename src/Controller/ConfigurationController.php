<?php

namespace App\Controller;

use App\Service\RoomApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ConfigurationController extends AbstractController
{
    /**
     * @Route("api/configuration/rooms/{propertyUid}")
     */
    public function getConfigRooms($propertyUid, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->getRooms(0, $propertyUid);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/configuration/roomstatus")
     */
    public function getRoomStatuses(LoggerInterface $logger,Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->getRoomStatuses();
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/configuration/roombedsizes")
     */
    public function getRoomBedSizes(LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->getRoomBedSizes();
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/configuration/removeimage/{imageId}")
     */
    public function removeImage($imageId, LoggerInterface $logger,Request $request, EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->removeImage($imageId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/configuration/markdefault/{imageId}")
     */
    public function markDefault($imageId, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->markDefault($imageId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }



}