<?php

namespace App\Controller;

use App\Entity\Reservations;
use App\Entity\ReservationStatus;
use App\Helpers\FormatHtml\CalendarHTML;
use App\Helpers\FormatHtml\InvoiceHTML;
use App\Helpers\FormatHtml\ReservationHtml;
use App\Service\BlockedRoomApi;
use App\Service\ReservationApi;
use App\Service\RoomApi;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ReservationController extends AbstractController
{

    /**
     * @Route("api/calendar")
     */
    public function getCalendar( LoggerInterface $logger, Request $request, EntityManagerInterface $entityManager): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $calendarHtml = new CalendarHTML($entityManager, $logger);
        $html = $calendarHtml->formatHtml();
        $response = array(
            'html' => $html,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/reservations/{period}")
     */
    public function getReservations($period,  LoggerInterface $logger, Request $request, EntityManagerInterface $entityManager, ReservationApi $reservationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $reservations = "";
        switch ($period) {
            case "future":
                $reservations = $reservationApi->getUpComingReservations();
                break;
            case "past":
                $reservations = $reservationApi->getPastReservations();
                break;
            case "checkout":
                $reservations = $reservationApi->getCheckOutReservation();
                break;
            case "stayover":
                $reservations = $reservationApi->getStayOversReservations();
                break;
            case "pending":
                $reservations = $reservationApi->getPendingReservations();
                break;
            default:
        }

        $reservationHtml = new ReservationHtml($entityManager, $logger);
        $html = $reservationHtml->formatHtml($reservations, $period);
        $response = array(
            'html' => $html,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;

    }

    /**
     * @Route("api/reservations/{reservationId}")
     */
    public function getReservationById($reservationId, LoggerInterface $logger, Request $request, EntityManagerInterface $entityManager, ReservationApi $reservationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $reservationApi->getReservationJson($reservationId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/reservations/{reservationId}/update/{field}/{newValue}")
     */
    public function updateReservation($reservationId, $field, $newValue, Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, ReservationApi $reservationApi, BlockedRoomApi $blockedRoomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        $reservation = $reservationApi->getReservation($reservationId);
        $responseArray[] = array();
        switch ($field) {
            case "status":
                if(is_int($newValue)){
                    $status = $entityManager->getRepository(ReservationStatus::class)->findOneBy(array('id' => $newValue));
                }else{
                    $status = $entityManager->getRepository(ReservationStatus::class)->findOneBy(array('name' => $newValue));
                }
                $reservation->SetStatus($status);
                if(strcmp($status->getName(), 'cancelled')){
                    $blockedRoomApi->deleteBlockedRoomByReservation($reservation);
                }
                break;
            case "check_in_time":
                $reservation->SetCheckInTime($newValue);
                break;
            case "check_out_time":
                $reservation->SetCheckOutTime($newValue);
                break;
            case "check_in_status":
                $now = new DateTime();
                if (strcmp($newValue, "checked_in") == 0) {
                    $logger->info("checked_in");
                    if ($reservationApi->isEligibleForCheckIn($reservation)) {

                        $reservation->setCheckInStatus($newValue);
                        $reservation->setCheckInTime($now->format("H:i"));
                    } else {
                        $responseArray[] = array(
                            'result_message' => "Please make sure the guest Id and phone number is captured",
                            'result_code' => 1
                        );
                        $logger->info(print_r($responseArray, true));
                        return $this->json($responseArray);
                    }
                } else if (strcmp($newValue, "checked_out") == 0) {
                    $logger->info("checked_out");
                    $due = $reservationApi->getAmountDue($reservation);
                    if ($due == 0) {
                        $reservation->setCheckInStatus($newValue);
                        $reservation->setCheckOutTime($now->format("H:i"));
                    } else {
                        $logger->info($due);
                        $responseArray[] = array(
                            'result_message' => "Please make sure the guest has settled their balance",
                            'result_code' => 1,
                            'due' => $due
                        );
                        $logger->info(print_r($responseArray, true));
                        return $this->json($responseArray);
                    }
                } else {
                    $responseArray[] = array(
                        'result_message' => "incorrect status provided",
                        'result_code' => 1
                    );
                    $logger->info(print_r($responseArray, true));
                    return $this->json($responseArray);
                }


                break;
            default:
                $responseArray[] = array(
                    'result_message' => "incorrect update field provided",
                    'result_code' => 1
                );
                $logger->info(print_r($responseArray, true));
                return $this->json($responseArray);
        }
        $response = $reservationApi->updateReservation($reservation);
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/reservations/{reservationId}/update/dates/{checkInDate}/{checkOutDate}")
     */
    public function updateReservationDates($reservationId, $checkInDate, $checkOutDate, Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, ReservationApi $reservationApi, BlockedRoomApi $blockedRoomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        $reservation = $reservationApi->getReservation($reservationId);
        $response = $reservationApi->updateReservationDate($reservation, $checkInDate, $checkOutDate, $blockedRoomApi);

        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/reservations/{reservationId}/update_room/{roomId}")
     */
    public function updateReservationRoom($reservationId, $roomId, Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, ReservationApi $reservationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        $reservation = $reservationApi->getReservation($reservationId);
        $response = $reservationApi->updateReservationRoom($reservation, $roomId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/reservations/{reservationId}/update_confirmation/{confirmationCode}")
     */
    public function updateReservationConfirmationCode($reservationId, $confirmationCode, Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, ReservationApi $reservationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        $reservation = $reservationApi->getReservation($reservationId);
        $response = $reservationApi->updateReservationOriginUrl($reservation, $confirmationCode);
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("public/reservations/create/{roomIds}/{guestName}/{phoneNumber}/{checkInDate}/{checkOutDate}/{email}", defaults={"email": ""})
     * @throws \Exception
     */
    public function creatReservation($roomIds, $guestName, $phoneNumber, $checkInDate, $checkOutDate, $email, Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, ReservationApi $reservationApi, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $reservationApi->createReservation($roomIds, $guestName, $phoneNumber, $email, $checkInDate, $checkOutDate);
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("public/invoice/{reservationId}")
     * @throws \Exception
     */
    public function getInvoiceDetails($reservationId, Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, ReservationApi $reservationApi, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $reservation = $reservationApi->getReservation($reservationId);
        $invoiceHtml = new InvoiceHTML($entityManager, $logger);
        $html = $invoiceHtml->formatHtml($reservation);
        $response = array(
            'html' => $html,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("public/reviews/send")
     * @throws \Exception
     */
    public function sendReviewRequest(Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, ReservationApi $reservationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $reservationApi->sendReviewRequest();
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("public/payfast_notify")
     * @throws \Exception
     */
    public function payfast_notify(Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, ReservationApi $reservationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $logger->info("reservation ID: " . $request->get('item_description'));
        $logger->info("amount paid: " . $request->get('amount_gross'));
        $response = array();
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

}