<?php

namespace App\Controller;

use App\Service\CommunicationApi;
use App\Service\PropertyApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
require_once(__DIR__ . '/../app/application.php');

class CommunicationController extends AbstractController
{
    /**
     * @Route("public/property/contact/{guestName}/{email}/{phoneNumber}/{message}")
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
     * @Route("public/sales/contact/{customerName}/{email}/{phoneNumber}/{message}")
     */
    public function contactSales($customerName, $email, $phoneNumber, $message, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, CommunicationApi $communicationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $message.= "<br> from : $customerName
            <br> phone number: $phoneNumber";
        $response = $communicationApi->sendEmailViaGmail(ALUVEAPP_ADMIN_EMAIL, ALUVEAPP_SALES_EMAIL, $message, "Message from customer", $email);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("public/sales/trial/{customerName}/{email}/{phoneNumber}/{hotelName}")
     */
    public function newTrial($customerName, $email, $phoneNumber, $hotelName, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, CommunicationApi $communicationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $message = "<br> from : $customerName
            <br> phone number: $phoneNumber
            <br> Hotel : $hotelName
            <br> email : $email";
        $response = $communicationApi->sendEmailViaGmail(ALUVEAPP_ADMIN_EMAIL, ALUVEAPP_SALES_EMAIL, $message, "Sales - New Trial", $email);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

}