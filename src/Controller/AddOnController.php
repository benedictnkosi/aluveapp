<?php

namespace App\Controller;

use App\Helpers\FormatHtml\ConfigAddonsHTML;
use App\Service\AddOnsApi;
use App\Service\PaymentApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class AddOnController extends AbstractController
{
    /**
     * @Route("api/addon/{addonid}/reservation/{reservationId}/quantity/{quantity}")
     */
    public function addPayment($addonid, $reservationId, $quantity, LoggerInterface $logger, EntityManagerInterface $entityManager, AddOnsApi $addOnsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if(!isset($_SESSION['PROPERTY_ID'])) {
            return $this->render('login.html');
        }
        $response = $addOnsApi->addAdOnToReservation($reservationId,$addonid, $quantity);
        return  $this->json($response);
    }

    /**
     * @Route("api/addon/configaddons")
     */
    public function getConfigAddOns(LoggerInterface $logger, EntityManagerInterface $entityManager, AddOnsApi $addOnsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $addOns = $addOnsApi->getAddOns();
        $configAddonsHTML = new ConfigAddonsHTML( $entityManager, $logger);
        $formattedHtml = $configAddonsHTML->formatHtml($addOns);
        return new Response(
            $formattedHtml
        );
    }

    /**
     * @Route("api/createaddon/{addOnName}/{addOnPrice}")
     */
    public function createAddon($addOnName, $addOnPrice, LoggerInterface $logger, EntityManagerInterface $entityManager, AddOnsApi $addOnsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if(!isset($_SESSION['PROPERTY_ID'])) {
            return $this->render('login.html');
        }
        $response = $addOnsApi->createAddOn($addOnName, $addOnPrice);
        return  $this->json($response);
    }


    /**
     * @Route("api/addon/delete/{addOnId}")
     */
    public function deleteAddOn($addOnId, LoggerInterface $logger, EntityManagerInterface $entityManager, AddOnsApi $addOnsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if(!isset($_SESSION['PROPERTY_ID'])) {
            return $this->render('login.html');
        }
        $response = $addOnsApi->deleteAddOn($addOnId);
        return  $this->json($response);
    }

    /**
     * @Route("api/addon/update/{addOnId}/{field}/{newValue}")
     */
    public function updateAddOn($addOnId, $field, $newValue, LoggerInterface $logger, EntityManagerInterface $entityManager, AddOnsApi $addOnsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $addOnsApi->updateAddOn($addOnId, $field, $newValue);
        return  $this->json($response);
    }

}