<?php

namespace App\Controller;

use App\Service\PaymentApi;
use App\Service\ReservationApi;
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
     * @Route("api/payment/{reservationId}/amount/{amount}/{paymentChannel}/{reference}")
     */
    public function addPayment($reservationId, $amount,  $paymentChannel, $reference, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PaymentApi $paymentApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $paymentApi->addPayment($reservationId, $amount, str_replace("_","/",$reference), $paymentChannel);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/paymentdelete/{paymentId}")
     */
    public function removePayment($paymentId, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PaymentApi $paymentApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $paymentApi->removePayment($paymentId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/discount/{reservationId}/amount/{amount}")
     */
    public function addDiscount($reservationId, $amount, LoggerInterface $logger, Request $request, PaymentApi $paymentApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $paymentApi->addDiscount($reservationId, $amount, "discount");
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }
    /**
     * @Route("public/payfast_notify")
     * @throws \Exception
     */
    public function payfast_notify(Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, ReservationApi $reservationApi, PaymentApi $paymentApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $logger->info("reservation ID: " . $request->get('item_description'));
        $logger->info("amount paid: " . $request->get('amount_gross'));
        $reservationId = $request->get('item_description');
        $amount = $request->get('amount_gross');

        $response = $paymentApi->addPayment($reservationId, $amount, "payfast", "payfast");
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("public/payfast_notify_test")
     * @throws \Exception
     */
    public function payfast_notify_test(Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, ReservationApi $reservationApi, PaymentApi $paymentApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $reservationId = "[74,75]";
        $amount = "10.00";

        $response = $paymentApi->addPayment($reservationId, $amount, "payfast", "payfast");
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/payment/total/cash/{startDate}/{endDate}/{channel}")
     */
    public function getTotalCashPayment($startDate, $endDate, $channel, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PaymentApi $paymentApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $paymentApi->getCashReport($startDate, $endDate, $channel);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/payment/total/cashtransactions/{startDate}/{endDate}/{channel}/{isGroup}")
     */
    public function getTotalCashPaymentByDay($startDate, $endDate,$channel, $isGroup, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PaymentApi $paymentApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (strcmp($isGroup, "true") === 0) {
            $response = $paymentApi->getCashReportByDay($startDate, $endDate, $channel);
        }else{
            $response = $paymentApi->getCashReportAllTransactions($startDate, $endDate, $channel);
        }

        $response = array(
            'html' => $response,
        );

        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }


}