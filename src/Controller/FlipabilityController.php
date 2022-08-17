<?php

namespace App\Controller;

use App\Service\BirdViewApi;
use App\Service\WebScrapperApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FlipabilityController extends AbstractController
{

    /**
     * @Route("/flipability")
     */
    public function home( LoggerInterface $logger, BirdViewApi $birdViewApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        return $this->render('flipability_home.html', [
            'title' => 'Bird View'
        ]);
    }

    /**
     * @Route("/properties")
     */
    public function properties( LoggerInterface $logger, BirdViewApi $birdViewApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        return $this->render('flipability_properties.html', [
            'title' => 'Properties'
        ]);
    }

    /**
     * @Route("/public/birdview/{type}/{value}/{bedrooms}/{bathrooms}/{erf}")
     */
    public function birdview($type, $value, $bedrooms,$bathrooms, $erf, LoggerInterface $logger, BirdViewApi $birdViewApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $birdViewApi->getHomePageSummary($type, $value, $bedrooms,$bathrooms, $erf);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("/public/properties/{type}/{value}/{bedrooms}/{bathrooms}/{erf}/{excludeLocation}",  defaults={"excludeLocation" = "NONE"})
     */
    public function propertiesView($type, $value, $bedrooms,$bathrooms, $erf, $excludeLocation, LoggerInterface $logger, BirdViewApi $birdViewApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $birdViewApi->getFlipableProperties($type, $value, $bedrooms,$bathrooms, $erf, $excludeLocation);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("/location/{location}/{filterType}/{percentageCheaper}/{bedrooms}/{bathrooms}/{erf}/{avgErf}")
     */
    public function location($location, $filterType, $percentageCheaper,  $bedrooms,$bathrooms ,$erf, $avgErf,LoggerInterface $logger, BirdViewApi $birdViewApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if(strcmp($percentageCheaper, "0")!==0){
            $responseArray = $birdViewApi->getLocationFlipableProperties($location, $filterType, $percentageCheaper, $bedrooms,$bathrooms, $erf, $avgErf);
        }else{
            $responseArray = $birdViewApi->getLocationProperties($location, $bedrooms,$bathrooms);
        }

        $logger->info("response array" . print_r($responseArray, true));
        $percentageCheaper = floatval($percentageCheaper) *100;
        if(strcmp($filterType, 'average')===0){
            $summaryMessage = "Showing properties that are $percentageCheaper% cheaper than the average price
            <br> Only showing property  with <b>$bedrooms+</b> bedrooms and  <b>$bathrooms+</b> bathrooms
            <br> The price for the average house is <b>R" . number_format((float)$responseArray[0]['average_price'], 0, '.', ' ') . "</b>
            <br> The stand size for the average house is <b>" . number_format((float)$responseArray[0]['average_erf'], 0, '.', '') . "m2</b>";
        }elseif (strcmp($percentageCheaper, "0")===0) {
            $summaryMessage = "Only showing property  with <b>$bedrooms+</b> bedrooms and  <b>$bathrooms+</b> bathrooms";
        }
        else {
            $summaryMessage = "Showing properties that are <b>$percentageCheaper%</b> cheaper than the  <b>$filterType%</b> most expensive houses
            <br> Only showing property  with <b>$bedrooms+</b> bedrooms and  <b>$bathrooms+</b> bathrooms
            <br> The price for the <b>$filterType% (".$responseArray[0]['percentile_count'] . " Properties)</b> most expensive houses start at <b>R" . number_format((float)$responseArray[0]['percentile_price'], 0, '.', ' ') . "</b>
            <br> The average stand size for the <b>$filterType% (".$responseArray[0]['percentile_count'] . " Properties)</b> most expensive houses is <b>" . number_format((float)$responseArray[0]['average_erf'], 0, '.', '') . "m2</b>";
        }

        return $this->render('flipability_location.html', [
            'location_summary_table' => $responseArray[0]['html'] ,
            'title' => $location,
            'summary' => $summaryMessage
        ]);
    }

    /**
     * @Route("/public/scrap/")
     */
    public function scrap(LoggerInterface $logger, WebScrapperApi $webScrapperApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $responseArray = $webScrapperApi->scrapPage();
        return new JsonResponse( $responseArray, 200, array());
    }
}