<?php

namespace App\Controller;

use App\Service\PropertyApi;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckController extends AbstractController
{


    /**
     * @Route("public/healthcheck")
     */
    public function checkSymfonyHealth(LoggerInterface $logger): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        $responseArray = array("results" => "ok");
        return new JsonResponse( $responseArray, 200, array());
    }

    /**
     * @Route("public/servertime")
     */
    public function serverTime(LoggerInterface $logger): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $now = new DateTime();
        $responseArray = array("time" => print_r($now));
        return new JsonResponse( $responseArray, 200, array());
    }
}