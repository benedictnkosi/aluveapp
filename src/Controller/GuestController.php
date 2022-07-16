<?php

namespace App\Controller;

use App\Service\GuestApi;
use App\Service\ReservationApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GuestController extends AbstractController
{
    /**
     * @Route("/api/guests/{guestId}", name="guests", defaults={"guestId": 0})
     */
    public function getGuests($guestId, LoggerInterface $logger, GuestApi $guestApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__ );
        $guests = $guestApi->getGuests($guestId);
        return  $this->json($guests);
    }

    /**
     * @Route("/api/guests/{guestId}/phone/{phoneNumber}")
     */
    public function updateGuest($guestId, $phoneNumber, LoggerInterface $logger, GuestApi $guestApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $guestApi->updateGuestPhoneNumber($guestId, $phoneNumber);
        return $this->json($response);
    }

    /**
     * @Route("/api/reservation/{resId}/blockguest/{reason}")
     */
    public function blockGuest($resId, $reason, LoggerInterface $logger, GuestApi $guestApi, ReservationApi $reservationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $reservation = $reservationApi->getReservation($resId);
        $response = $guestApi->blockGuest($reservation->getGuest()->getId(), str_replace("+", "", $reason));
        return $this->json($response);
    }

    /**
     * @Route("/api/reservation/{resId}/idnumber/{idNumber}")
     */
    public function updateGuestIdNumber($resId, $idNumber, LoggerInterface $logger, GuestApi $guestApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $guestApi->updateGuestPhoneNumber($resId, $idNumber);
        return $this->json($response);
    }


}