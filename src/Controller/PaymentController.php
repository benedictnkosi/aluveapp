<?php

namespace App\Controller;

use App\Service\EmployeeApi;
use App\Service\PaymentApi;
use App\Service\ReservationApi;
use JMS\Serializer\SerializerBuilder;
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
     * @Route("api/payment/add")
     */
    public function addPayment(LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PaymentApi $paymentApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('post')) {
            return new JsonResponse("Internal server error" , 500, array());
        }

        $response = $paymentApi->addPayment($request->get('id'), $request->get('amount'), str_replace("_","/",$request->get('reference')), $request->get('channel'));
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("admin_api/payment/{paymentId}/delete")
     */
    public function removePayment($paymentId, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PaymentApi $paymentApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('remove')) {
            return new JsonResponse("Internal server error" , 500, array());
        }
        $response = $paymentApi->removePayment($paymentId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/discount/add")
     */
    public function addDiscount(LoggerInterface $logger, Request $request, PaymentApi $paymentApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        if (!$request->isMethod('post')) {
            return new JsonResponse("Internal server error" , 500, array());
        }

        $response = $paymentApi->addDiscount($request->get('id'), $request->get('amount'), "discount");
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

    /**
     * @Route("api/json/payment/{id}")
     */
    public function getPaymentJson( $id, LoggerInterface $logger, PaymentApi $api): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $payment = $api->getPayment($id);

        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($payment, 'json');

        $logger->info($jsonContent);
        return new JsonResponse($jsonContent , 200, array(), true);
    }


}