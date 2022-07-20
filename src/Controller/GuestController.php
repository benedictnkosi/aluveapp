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
        $propertyUid =   $propertyApi->getPropertyUidByHost($request);
        $response = $guestApi->getGuests($filterValue, $propertyUid);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/guests/{guestId}/phone/{phoneNumber}")
     */
    public function updateGuest($guestId, $phoneNumber, LoggerInterface $logger,Request $request,GuestApi $guestApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $guestApi->updateGuestPhoneNumber($guestId, $phoneNumber);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/reservation/{resId}/blockguest/{reason}")
     */
    public function blockGuest($resId, $reason, LoggerInterface $logger, Request $request,GuestApi $guestApi, ReservationApi $reservationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $reservation = $reservationApi->getReservation($resId);
        $response = $guestApi->blockGuest($reservation->getGuest()->getId(), str_replace("+", "", $reason));
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/reservation/{resId}/idnumber/{idNumber}")
     */
    public function updateGuestIdNumber($resId, $idNumber, LoggerInterface $logger, Request $request,GuestApi $guestApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $guestApi->updateGuestPhoneNumber($resId, $idNumber);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }


}