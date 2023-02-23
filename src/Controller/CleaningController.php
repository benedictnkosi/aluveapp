<?php

namespace App\Controller;

use App\Helpers\FormatHtml\CalendarHTML;
use App\Service\CleaningApi;
use App\Service\ReservationApi;
use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CleaningController extends AbstractController
{
    /**
     * @Route("api/cleanings/{roomId}")
     */
    public function getCleanings($roomId, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, CleaningApi $cleaningApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $html = $cleaningApi->getCleaningsByRoom($roomId);
        $response = array(
            'html' => $html,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }


    /**
     * @Route("api/outstandingcleanings/today")
     */
    public function getOutstandingCleaningsForToday(LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, CleaningApi $cleaningApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $html = $cleaningApi->getOutstandingCleaningsForToday();
        $response = array(
            'html' => $html,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/cleaning/add")
     */
    public function addCleaningToReservation(LoggerInterface $logger,Request $request, EntityManagerInterface $entityManager, CleaningApi $cleaningApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('post')) {
            return new JsonResponse("Internal server error" , 500, array());
        }

        $response = $cleaningApi->addCleaningToReservation($request->get('id'),$request->get('employee_id'));
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }


    /**
     * @Route("api/json/cleaning/{id}")
     */
    public function getCleaningJson( $id, LoggerInterface $logger, CleaningApi $api): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $cleaning = $api->getCleaningById($id);

        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($cleaning, 'json');

        $logger->info($jsonContent);
        return new JsonResponse($jsonContent , 200, array(), true);
    }


}