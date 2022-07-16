<?php

namespace App\Controller;

use App\Service\PaymentApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends AbstractController
{
    /**
     * @Route("api/payment/{reservationId}/amount/{amount}")
     */
    public function addPayment($reservationId, $amount, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PaymentApi $paymentApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $paymentApi->addPayment($reservationId, $amount);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }
}