<?php

namespace App\Controller;

use App\Service\EmailService;
use App\Helpers\FormatHtml\BlockedRoomsHTML;
use App\Helpers\FormatHtml\ConfigIcalLinksLogsHTML;
use App\Service\GuestApi;
use App\Service\ICalApi;
use App\Service\RoomApi;
use Doctrine\ORM\EntityManagerInterface;
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
     * @Route("public/ical/import/{roomId}")
     */
    public function importIcalReservations($roomId, LoggerInterface $logger, Request $request, ICalApi $iCalApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $iCalApi->importIcalForRoom($roomId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("public/ical/importall")
     */
    public function importAllRoomsIcalReservations(LoggerInterface $logger, Request $request, ICalApi $iCalApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $iCalApi->importIcalForAllRooms();
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("public/ical/export/{roomId}")
     */
    public function exportIcalReservations($roomId, LoggerInterface $logger, Request $request, ICalApi $iCalApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $ical = $iCalApi->exportIcalForRoom($roomId);
        $response = new Response($ical);
        $response->headers->add(array('Content-type' => 'text/calendar; charset=utf-8', 'Content-Disposition' => 'inline; filename=aluve_' . $roomId . '.ics'));
        return $response;
    }

    /**
     * @Route("api/ical/links/{roomId}/{link}")
     */
    public function addNewChannel($roomId, $link, LoggerInterface $logger, Request $request, ICalApi $iCalApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $iCalApi->addNewChannel($roomId, str_replace("###", "/", $link));
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/ical/remove/{linkId}")
     */
    public function removeChannel($linkId, LoggerInterface $logger, Request $request, ICalApi $iCalApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $iCalApi->removeIcalLink($linkId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/ical/logs")
     */
    public function getIcalSynchLogs( LoggerInterface $logger, EntityManagerInterface $entityManager, Request $request, ICalApi $iCalApi, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $configIcalLinksLogsHTML = new ConfigIcalLinksLogsHTML($entityManager, $logger);
        $html = $configIcalLinksLogsHTML->formatHtml( $roomApi, $iCalApi);
        $response = array(
            'html' => $html,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/public/updateairbnbguest")
     */
    public function updateAirbnbGuest(ICalApi $ICalApi, GuestApi $guestApi, LoggerInterface $logger): JsonResponse
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $ICalApi->updateAirbnbGuestUsingGmail($guestApi);
        return new JsonResponse($response, 200, array());
    }

}