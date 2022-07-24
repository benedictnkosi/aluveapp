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
     * @Route("/api/schedulemessages/today/{propertyUid}")
     */
    public function sendScheduleMessagesDayOfCheckIn($propertyUid, LoggerInterface $logger, Request $request,ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->sendScheduledMessages("Day of check-in", $propertyUid);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/schedulemessages/tomorrow/{propertyUid}")
     */
    public function sendScheduleMessagesDayBeforeCheckIn($propertyUid,LoggerInterface $logger, Request $request,ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->sendScheduledMessages("Day before check-in", $propertyUid);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/schedulemessages/week/{propertyUid}")
     */
    public function sendScheduleMessagesWeekBeforeCheckIn($propertyUid, LoggerInterface $logger, Request $request,ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->sendScheduledMessages("Week before check-in", $propertyUid);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/schedulemessages/templates/{propertyUid}")
     */
    public function getMessageTemplates($propertyUid,LoggerInterface $logger, Request $request,ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->getScheduleTemplates($propertyUid);
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
     * @Route("/api/schedulemessages/createtemplate/{name}/{message}/{propertyUid}")
     */
    public function createMessageTemplate($propertyUid, $name, $message,Request $request,LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->createMessageTemplate($name, $message, $propertyUid);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/schedulemessages/{propertyUid}")
     */
    public function getScheduledMessages($propertyUid, LoggerInterface $logger,Request $request, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->getScheduledMessages($propertyUid);
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