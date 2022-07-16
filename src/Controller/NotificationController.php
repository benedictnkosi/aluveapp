<?php

namespace App\Controller;

use App\Service\NotificationApi;
use App\Service\OccupancyApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController  extends AbstractController
{
    /**
     * @Route("api/notifications")
     */
    public function getNotifications(LoggerInterface $logger, EntityManagerInterface $entityManager, NotificationApi $notificationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $notificationApi->getNotifications();
        return new Response(
            $response
        );
    }
}