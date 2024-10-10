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
     * @Route("noauth/property/contact/{guestName}/{email}/{phoneNumber}/{message}")
     */
    public function contactProperty($guestName, $email, $phoneNumber, $message, LoggerInterface $logger, Request $request, EntityManagerInterface $entityManager, PropertyApi $propertyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $propertyApi->contactUs($guestName, $email, $phoneNumber, $message, $request);
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("noauth/sales/contact/{customerName}/{email}/{phoneNumber}/{message}")
     */
    public function contactSales($customerName, $email, $phoneNumber, $message, LoggerInterface $logger, Request $request, EntityManagerInterface $entityManager, CommunicationApi $communicationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $message .= "<br> from : $customerName
            <br> phone number: $phoneNumber";
        $response = $communicationApi->sendEmailViaGmail(ALUVEAPP_ADMIN_EMAIL, ALUVEAPP_SALES_EMAIL, $message, "Aluve App - Message from customer", $email);
        $responseArray = array();
        if ($response[0]['result_code'] === 0) {
            $responseArray[] = array(
                'result_message' => 'Thank you for your message. Our team will be in touch soon',
                'result_code' => 0
            );
        } else {
            $responseArray[] = array(
                'result_message' => 'Oops, failed to send email to our team. Please try again later',
                'result_code' => 1
            );
        }
        $callback = $request->get('callback');
        $response = new JsonResponse($responseArray, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("noauth/sales/trial/{customerName}/{email}/{phoneNumber}/{hotelName}")
     */
    public function newTrial($customerName, $email, $phoneNumber, $hotelName, LoggerInterface $logger, Request $request, EntityManagerInterface $entityManager, CommunicationApi $communicationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $message = "<br> from : $customerName
            <br> phone number: $phoneNumber
            <br> Hotel : $hotelName
            <br> email : $email";
        $response = $communicationApi->sendEmailViaGmail(ALUVEAPP_ADMIN_EMAIL, ALUVEAPP_SALES_EMAIL, $message, "Sales - New Trial", $email);
        $responseArray = array();
        if ($response[0]['result_code'] === 0) {
            $responseArray[] = array(
                'result_message' => 'Thank you for your interest in Aluve App. Our sales team will be in touch soon',
                'result_code' => 0
            );
        } else {
            $responseArray[] = array(
                'result_message' => 'Oops, failed to send email to our sales team. Please try again later',
                'result_code' => 1
            );
        }
        $callback = $request->get('callback');
        $response = new JsonResponse($responseArray, 200, array());
        $response->setCallback($callback);
        return $response;
    }

}