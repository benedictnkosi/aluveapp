<?php

namespace App\Controller;

use App\Helpers\FormatHtml\CalendarHTML;
use App\Service\ScheduleMessageApi;
use App\Service\StatsApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ScheduledMessagesController extends AbstractController
{
    /**
     * @Route("/public/schedulemessages/checkin")
     */
    public function sendScheduleMessagesDayOfCheckIn( LoggerInterface $logger, Request $request,ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->sendScheduledMessages("Day of check-in");
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/public/schedulemessages/daybefore")
     */
    public function sendScheduleMessagesDayBeforeCheckIn(LoggerInterface $logger, Request $request,ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->sendScheduledMessages("Day before check-in");
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/public/schedulemessages/weekbefore")
     */
    public function sendScheduleMessagesWeekBeforeCheckIn( LoggerInterface $logger, Request $request,ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->sendScheduledMessages("Week before check-in");
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/schedulemessages/templates")
     */
    public function getMessageTemplates(LoggerInterface $logger, Request $request,ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $html = $scheduleMessageApi->getScheduleTemplates();
        $response = array(
            'html' => $html,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/schedulemessages/schedules")
     */
    public function getMessageSchedules(LoggerInterface $logger, Request $request,ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->getScheduleTimes();
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/schedulemessages/variables")
     */
    public function getMessageVariables(LoggerInterface $logger, Request $request,ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->getMessageVariables();
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/schedulemessages/create/{messageId}/{scheduleId}/{rooms}")
     */
    public function createScheduleMessage($messageId, $scheduleId, $rooms, Request $request,LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->createScheduleMessage($messageId, $scheduleId, $rooms);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/schedulemessages/createtemplate/{name}/{message}")
     */
    public function createMessageTemplate( $name, $message,Request $request,LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->createMessageTemplate($name, $message);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/schedulemessages")
     */
    public function getScheduledMessages( LoggerInterface $logger,Request $request, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $html = $scheduleMessageApi->getScheduledMessages();
        $response = array(
            'html' => $html,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/schedulemessages/delete/{scheduleMessageId}")
     */
    public function deleteScheduledMessages($scheduleMessageId, Request $request,LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->deleteScheduledMessages($scheduleMessageId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/schedulemessages/template/{templateId}")
     */
    public function getTemplateMessage($templateId, Request $request,LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->getTemplateMessage($templateId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

}