<?php

namespace App\Controller;

use App\Service\PaymentApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends AbstractController
{
    /**
     * @Route("api/payment/{reservationId}/amount/{amount}")
     */
    public function addPayment($reservationId, $amount, LoggerInterface $logger, EntityManagerInterface $entityManager, PaymentApi $paymentApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $paymentApi->addPayment($reservationId, $amount);
        return  $this->json($response);
    }
}