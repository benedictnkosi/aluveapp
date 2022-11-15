<?php

namespace App\Controller;

use App\Service\GuestApi;
use App\Service\PropertyApi;
use App\Service\ReservationApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class GuestController extends AbstractController
{
    /**
     * @Route("/api/guests/{filterValue}", name="guests", defaults={"guestId": 0})
     */
    public function getGuests($filterValue, LoggerInterface $logger, Request $request, GuestApi $guestApi, PropertyApi $propertyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__ );
        $response = $guestApi->getGuests($filterValue);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/guest/{guestId}/phone/{phoneNumber}")
     */
    public function updateGuestPhone($guestId, $phoneNumber, LoggerInterface $logger,Request $request,GuestApi $guestApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $guestApi->updateGuestPhoneNumber($guestId, $phoneNumber);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/guest/{guestId}/email/{email}")
     */
    public function updateGuestEmail($guestId, $email, LoggerInterface $logger,Request $request,GuestApi $guestApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $guestApi->updateGuestEmail($guestId, $email);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/guest/{guestId}/idnumber/{idNumber}")
     */
    public function updateGuestIdNumber($guestId, $idNumber, LoggerInterface $logger, Request $request,GuestApi $guestApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $guestApi->updateGuestIdNumber($guestId, $idNumber);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }


    /**
     * @Route("/api/guests/airbnbname/{confirmationCode}/{name}")
     */
    public function createAirbnbGuest($confirmationCode, $name, LoggerInterface $logger,Request $request,GuestApi $guestApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $guestApi->createAirbnbGuest($confirmationCode, urldecode($name));
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

}