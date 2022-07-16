<?php

namespace App\Controller;

use App\Helpers\FormatHtml\CalendarHTML;
use App\Service\ScheduleMessageApi;
use App\Service\StatsApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScheduledMessagesController extends AbstractController
{
    /**
     * @Route("/api/schedulemessages/today")
     */
    public function sendScheduleMessagesDayOfCheckIn(LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->sendScheduledMessages("Day of check-in");
        return $this->json($response);
    }

    /**
     * @Route("/api/schedulemessages/tomorrow")
     */
    public function sendScheduleMessagesDayBeforeCheckIn(LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->sendScheduledMessages("Day before check-in");
        return $this->json($response);
    }

    /**
     * @Route("/api/schedulemessages/week")
     */
    public function sendScheduleMessagesWeekBeforeCheckIn(LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->sendScheduledMessages("Week before check-in");
        return $this->json($response);
    }

    /**
     * @Route("/api/schedulemessages/templates")
     */
    public function getMessageTemplates(LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->getScheduleTemplates();
        return new Response(
            $response
        );
    }

    /**
     * @Route("/api/schedulemessages/schedules")
     */
    public function getMessageSchedules(LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->getScheduleTimes();
        return $this->json($response);
    }

    /**
     * @Route("/api/schedulemessages/variables")
     */
    public function getMessageVariables(LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->getMessageVariables();
        return $this->json($response);
    }

    /**
     * @Route("/api/schedulemessages/create/{messageId}/{scheduleId}/{rooms}")
     */
    public function createScheduleMessage($messageId, $scheduleId, $rooms, LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->createScheduleMessage($messageId, $scheduleId, $rooms);
        return $this->json($response);
    }

    /**
     * @Route("/api/schedulemessages/createtemplate/{name}/{message}/")
     */
    public function createMessageTemplate($name, $message, LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->createMessageTemplate($name, $message);
        return $this->json($response);
    }

    /**
     * @Route("/api/schedulemessages")
     */
    public function getScheduledMessages(LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->getScheduledMessages();
        return new Response(
            $response
        );
    }

    /**
     * @Route("/api/schedulemessages/delete/{scheduleMessageId}")
     */
    public function deleteScheduledMessages($scheduleMessageId, LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->deleteScheduledMessages($scheduleMessageId);
        return $this->json($response);
    }


    /**
     * @Route("/api/schedulemessages/template/{templateId}")
     */
    public function getTemplateMessage($templateId, LoggerInterface $logger, ScheduleMessageApi $scheduleMessageApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $scheduleMessageApi->getTemplateMessage($templateId);
        return $this->json($response);
    }

}