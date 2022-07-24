<?php

namespace App\Controller;

use App\Service\PropertyApi;
use App\Service\RoomApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PropertyController extends AbstractController
{


    /**
     * @Route("api/property/contact/{guestName}/{email}/{phoneNumber}/{message}")
     */
    public function contactProperty($guestName, $email, $phoneNumber, $message, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PropertyApi $propertyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $propertyApi->contactUs($guestName, $email, $phoneNumber, $message,$request);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/property/terms/{propertyUid}" , defaults={"propertyUid": "none"})
     */
    public function getPropertyTerms($propertyUid, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PropertyApi $propertyApi, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $propertyApi->getPropertyTerms($roomApi, $propertyUid, $request);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/property/terms/update/{propertyUid}/{terms}")
     */
    public function updatePropertyTerms($propertyUid, $terms, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PropertyApi $propertyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $propertyApi->updatePropertyTerms($propertyUid, $terms);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

}