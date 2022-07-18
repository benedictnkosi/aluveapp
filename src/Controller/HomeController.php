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

class HomeController extends AbstractController
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
     * @Route("/api/logout")
     */
    public function logout(LoggerInterface $logger, Request $request, SecurityApi $securityApi ): Response
    {
        $logger->info("Starting Method: " . __METHOD__ );
        $response = $securityApi->logout();
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/api/isloggedin")
     */
    public function isLoggedIn(LoggerInterface $logger,  Request $request, EntityManagerInterface $entityManager, SecurityApi $securityApi): JsonResponse
    {
        $logger->info("Starting Method: " . __METHOD__ );
        $response = $securityApi->isloggedin();
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("/{page}", name="page", defaults={"page": "index.html"})
     */
    public function home($page, Request $request, LoggerInterface $logger,EntityManagerInterface $entityManager, RoomApi $roomApi, ReservationApi $reservationApi, AddOnsApi $addOnsApi, PaymentApi $paymentApi): Response
    {
        $roomId = $request->query->get('id');
        $reservation_id = $request->query->get('reservation');

        if($roomId != null){
            $room = $roomApi->getRoom($roomId);
            if($room === null){
                return $this->render("index.html");
            }

            $roomImages = $roomApi->getRoomImages($roomId);
            $roomImagesHtml = new RoomImagesHTML($entityManager, $logger);
            $imagesHtml = $roomImagesHtml->formatHtml($roomImages);
            $logger->info("images html: " . $imagesHtml );
            return $this->render($page,
                ['price'=>$room->getPrice(),
                    'sleeps'=>$room->getSleeps(),
                    'bed'=>$room->getBed()->getName(),
                    'description'=> $room->getDescription(),
                    'room_name'=>$room->getName(),
                    'images_slide'=>$imagesHtml
                    ]);
        }else if($reservation_id != null){
            $reservation = $reservationApi->getReservation($reservation_id);
            $totalDays = intval($reservation->getCheckIn()->diff($reservation->getCheckOut())->format('%a'));
            $totalPriceForAccommodation = (intVal($reservation->getRoom()->getPrice()) * $totalDays);
            $totalForAddOns = $addOnsApi->getAddOnsTotal($reservation_id);
            $totalForAll = intVal($totalPriceForAccommodation) + intVal($totalForAddOns);
            $totalPayments = $paymentApi->getReservationPaymentsTotal($reservation_id);
            $invoiceOrReceipt = "INVOICE";
            if($totalPayments > 0){
                $invoiceOrReceipt = "RECEIPT";
            }
            return $this->render($page,
                ['invoice_or_receipt'=>$invoiceOrReceipt,
                    'reservation_id'=>$reservation_id,
                    'created'=>$reservation->getReceivedOn()->format("Y-m-d"),
                    'guest_name'=>$reservation->getGuest()->getName(),
                    'guest_phone'=> $reservation->getGuest()->getPhoneNumber(),
                    'guest_email'=> $reservation->getGuest()->getEmail(),
                    'room_name'=>$reservation->getRoom()->getName(),
                    'check_in'=>$reservation->getCheckIn()->format("d M Y"),
                    'check_out'=>$reservation->getCheckOut()->format("d M Y"),
                    'nights'=>$totalDays,
                    'price_per_night'=> "R" . number_format((float)$reservation->getRoom()->getPrice(), 2, '.', '')  ,
                    'total_for_room'=> "R" . number_format((float)$totalPriceForAccommodation, 2, '.', '')  ,
                    'addOns'=>$addOnsApi->getAddOnsForInvoice($reservation_id),
                    'due'=> "R" . number_format((float)$paymentApi->getTotalDue($reservation_id), 2, '.', '')  ,
                    'total_for_rooms_addons'=>  "R" . number_format((float)$totalForAll, 2, '.', '') ,
                    'payments'=>$paymentApi->getReservationPaymentsHtml($reservation_id),
                    'total_payments'=> "-R" . number_format((float)$totalPayments, 2, '.', '')
                   /* 'bank_name'=>BANK_NAME,
                    'account_type'=>ACCOUNT_TYPE,
                    'account_number'=>ACCOUNT_NUMBER,
                    'branch_code'=>BRANCH_CODE,
                    'company_name'=>COMPANY_NAME,
                    'company_address_line_1'=>COMPANY_ADDRESS,
                    'company_address_suburb'=>COMPANY_ADDRESS_SUBURB,
                    'company_address_city'=>COMPANY_ADDRESS_CITY,
                    'company_phone'=>COMPANY_PHONE_NUMBER,
                    'company_email'=>EMAIL_ADDRESS*/
                ]);
        }else{
            if (str_contains($page, 'room.html')) {
                return $this->render("index.html");
            }
            return $this->render($page);
        }

    }

}