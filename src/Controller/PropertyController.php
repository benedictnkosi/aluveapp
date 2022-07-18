<?php

namespace App\Controller;

use App\Service\PropertyApi;
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
     * @Route("api/runcommand/clear")
     */
    public function runCommand(LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PropertyApi $propertyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $command = 'git --version';
        $result = trim(exec($command));
        $responseArray[] = array(
            'command' =>  $command,
            'result_message' => $result,
            'result_code' => 0
        );
        return new JsonResponse( $responseArray, 200, array());
    }

    /**
     * @Route("api/property/contact/{propertyId}/{guestName}/{email}/{phoneNumber}/{message}")
     */
    public function getPropertyDetails($propertyId, $guestName, $email, $phoneNumber, $message, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PropertyApi $propertyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $propertyApi->contactUs($propertyId, $guestName, $email, $phoneNumber, $message,);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }
    /**
     * @Route("api/property/getid")
     */
    public function getPropertyIdByServerName( LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PropertyApi $propertyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $propertyApi->getPropertyIdByServerName($request);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }


}