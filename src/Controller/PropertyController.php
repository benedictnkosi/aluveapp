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
     * @Route("public/property/terms")
     */
    public function getPropertyTerms( LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PropertyApi $propertyApi, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $propertyApi->getPropertyTerms($roomApi,  $request);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/property/terms/update/{terms}")
     */
    public function updatePropertyTerms( $terms, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PropertyApi $propertyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $propertyApi->updatePropertyTerms( $terms);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/property/severname")
     */
    public function getPropertyServerName( LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PropertyApi $propertyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $propertyApi->getPropertyServerName();
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

}