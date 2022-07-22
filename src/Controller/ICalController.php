<?php

namespace App\Controller;

use App\Service\ICalApi;
use PhpImap\Mailbox;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ICalController extends AbstractController
{
    /**
     * @Route("api/ical/import/{roomId}")
     */
    public function importIcalReservations($roomId, LoggerInterface $logger, Request $request,ICalApi $iCalApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $iCalApi->importIcalForRoom($roomId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/ical/export/{roomId}")
     */
    public function exportIcalReservations($roomId, LoggerInterface $logger, Request $request,ICalApi $iCalApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $ical = $iCalApi->exportIcalForRoom($roomId);
        $response = new Response($ical);
        //$response->headers->remove('Content-Disposition');
        $response->headers->add(array('Content-type'=>'text/calendar; charset=utf-8',  'Content-Disposition' => 'inline; filename=aluve_'.$roomId.'.ics'));
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }
}