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
     * @Route("api/ical/import/{roomId}")
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
     * @Route("api/ical/importall")
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
     * @Route("api/ical/export/{roomId}")
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
     * @Route("api/ical/logs/{propertyUid}")
     */
    public function getIcalSynchLogs($propertyUid, LoggerInterface $logger, EntityManagerInterface $entityManager, Request $request, ICalApi $iCalApi, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $configIcalLinksLogsHTML = new ConfigIcalLinksLogsHTML($entityManager, $logger);
        $html = $configIcalLinksLogsHTML->formatHtml($propertyUid, $roomApi, $iCalApi);
        $response = array(
            'html' => $html,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/updateairbnbguest")
     */
    public function updateairbnbguest(ICalApi $ICalApi, GuestApi $guestApi, LoggerInterface $logger)
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $ICalApi->updateAirbnbGuestUsingGmail($guestApi);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("/api/gmailservice")
     * @throws \Google\Exception
     */
    public function gmailservice(ICalApi $ICalApi, LoggerInterface $logger)
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $ICalApi->getEmails();
        return new JsonResponse($response, 200, array());
    }

}