<?php

namespace App\Controller;

use App\Helpers\FormatHtml\ConfigAddonsHTML;
use App\Service\AddOnsApi;
use App\Service\PaymentApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class AddOnController extends AbstractController
{
    /**
     * @Route("api/addon/{addonid}/reservation/{reservationId}/quantity/{quantity}")
     */
    public function addPayment($addonid, $reservationId, $quantity, Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, AddOnsApi $addOnsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if(!isset($_SESSION['PROPERTY_ID'])) {
            return $this->render('login.html');
        }
        $response = $addOnsApi->addAdOnToReservation($reservationId,$addonid, $quantity);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/addon/configaddons")
     */
    public function getConfigAddOns(LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, AddOnsApi $addOnsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $addOns = $addOnsApi->getAddOns();
        $configAddonsHTML = new ConfigAddonsHTML( $entityManager, $logger);
        $formattedHtml = $configAddonsHTML->formatHtml($addOns);
        $callback = $request->get('callback');
        $response = new JsonResponse($formattedHtml , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/createaddon/{addOnName}/{addOnPrice}")
     */
    public function createAddon($addOnName, $addOnPrice, Request $request,LoggerInterface $logger, EntityManagerInterface $entityManager, AddOnsApi $addOnsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $addOnsApi->createAddOn($addOnName, $addOnPrice);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }


    /**
     * @Route("api/addon/delete/{addOnId}")
     */
    public function deleteAddOn($addOnId, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, AddOnsApi $addOnsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if(!isset($_SESSION['PROPERTY_ID'])) {
            return $this->render('login.html');
        }
        $response = $addOnsApi->deleteAddOn($addOnId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/addon/update/{addOnId}/{field}/{newValue}")
     */
    public function updateAddOn($addOnId, $field, $newValue, Request $request,LoggerInterface $logger, EntityManagerInterface $entityManager, AddOnsApi $addOnsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $addOnsApi->updateAddOn($addOnId, $field, $newValue);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

}