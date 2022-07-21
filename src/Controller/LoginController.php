<?php

namespace App\Controller;

use App\Helpers\FormatHtml\RoomImagesHTML;
use App\Service\AddOnsApi;
use App\Service\PaymentApi;
use App\Service\ReservationApi;
use App\Service\RoomApi;
use App\Service\SecurityApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


require_once(__DIR__ . '/../app/application.php');

class LoginController extends AbstractController
{

    /**
     * @Route("/api/login/{secretPin}")
     */
    public function login($secretPin, LoggerInterface $logger,Request $request, EntityManagerInterface $entityManager, SecurityApi $securityApi ): Response
    {
        $logger->info("Starting Method: " . __METHOD__ );
        $response = $securityApi->login($secretPin);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/isloggedin/{propertyUid}")
     */
    public function isLoggedIn($propertyUid, LoggerInterface $logger,  Request $request, EntityManagerInterface $entityManager, SecurityApi $securityApi): JsonResponse
    {
        $logger->info("Starting Method: " . __METHOD__ );
        $response = $securityApi->isloggedin($propertyUid);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

}