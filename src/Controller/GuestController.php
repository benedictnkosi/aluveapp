<?php

namespace App\Controller;

use App\Helpers\FormatHtml\ConfigEmployeesHTML;
use App\Helpers\FormatHtml\ConfigGuestsHTML;
use App\Helpers\SMSHelper;
use App\Service\CommunicationApi;
use App\Service\EmployeeApi;
use App\Service\GuestApi;
use App\Service\PropertyApi;
use App\Service\ReservationApi;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerBuilder;
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
        $guestApi->sendBookDirectSMS($guestId);

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


    /**
     * @Route("api/config/guests/{nameFilter}")
     */
    public function getConfigGuests( $nameFilter, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, GuestApi $guestApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $guests = $guestApi->getConfigGuests($nameFilter);
        $logger->info("calling Method: formatHtml" );
        $configGuestsHTML = new ConfigGuestsHTML( $entityManager, $logger);
        $html = $configGuestsHTML->formatHtml($guests);
        $response = array(
            'html' => $html,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }


    /**
     * @Route("api/guest/update/{guestId}/{field}/{newValue}")
     */
    public function updateGuest($guestId, $field, $newValue, Request $request,LoggerInterface $logger, EntityManagerInterface $entityManager, guestApi $guestApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = match ($field) {
            "name" => $guestApi->updateGuestName($guestId, $newValue),
            "rewards" => $guestApi->updateGuestRewards($guestId, $newValue),
            "phoneNumber" => $guestApi->updateGuestPhoneNumber($guestId, $newValue),
            default => array(
                'result_message' => "field not found",
                'result_code' => 1
            ),
        };


        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }


    /**
     * @Route("public/json/guest/{id}")
     */
    public function getGuestJson( $id, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, GuestApi $guestApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $guest = $guestApi->getGuestById($id);

        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($guest, 'json');

        $logger->info($jsonContent);
        return new JsonResponse($jsonContent , 200, array(), true);
    }


}