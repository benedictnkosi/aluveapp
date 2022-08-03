<?php

namespace App\Controller;

use App\Service\NotificationApi;
use App\Service\OccupancyApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class NotificationController  extends AbstractController
{
    /**
     * @Route("api/notifications")
     */
    public function getNotifications( LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, NotificationApi $notificationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $html = $notificationApi->getNotifications();
        $response = array(
            'html' => $html,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }
}