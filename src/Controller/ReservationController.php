<?php

namespace App\Controller;

use App\Entity\Reservations;
use App\Entity\ReservationStatus;
use App\Helpers\FormatHtml\CalendarHTML;
use App\Helpers\FormatHtml\InvoiceHTML;
use App\Helpers\FormatHtml\ReservationsHtml;
use App\Helpers\FormatHtml\SingleReservationHtml;
use App\Service\AddOnsApi;
use App\Service\BlockedRoomApi;
use App\Service\GuestApi;
use App\Service\NotesApi;
use App\Service\PaymentApi;
use App\Service\ReservationApi;
use App\Service\RoomApi;
use DateTime;
use JMS\Serializer\SerializerBuilder;
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
     * @Route("public/reservations/checkout/json/{propertyId}")
     */
    public function getJsonCheckOutReservations($propertyId,  LoggerInterface $logger, Request $request, EntityManagerInterface $entityManager, ReservationApi $reservationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $reservations = $reservationApi->getCheckOutReservation($propertyId);
        $logger->info("back from other call");
        $logger->info("reservations count " . sizeof($reservations) );
        return new JsonResponse(print_r($reservations), 200, array());

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
                $reservations = $reservationApi->getUpComingReservations(0, true,true);
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

        $reservationHtml = new ReservationsHtml($entityManager, $logger);
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
     * @Route("api/reservation_html/{reservationId}")
     */
    public function getReservationByIdHtml($reservationId,  LoggerInterface $logger, Request $request, EntityManagerInterface $entityManager, ReservationApi $reservationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $reservation = $reservationApi->getReservation($reservationId);
        $reservationHtml = new SingleReservationHtml($entityManager, $logger);
        $html = $reservationHtml->formatHtml($reservation);
        $response = array(
            'html' => $html,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;

    }


    /**
     * @Route("public/reservation/{reservationId}")
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
    public function updateReservation($reservationId, $field, $newValue, Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, ReservationApi $reservationApi, BlockedRoomApi $blockedRoomApi,  NotesApi $notesApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        $reservation = $reservationApi->getReservation($reservationId);
        $responseArray = array();
        $now = new DateTime();
        switch ($field) {
            case "status":
                $notesApi->addNote($reservation->getId(), "Status Changed to " .$newValue. " at " . $now->format("Y-m-d H:i"));
                if(intval($newValue) !== 0){
                    $status = $entityManager->getRepository(ReservationStatus::class)->findOneBy(array('id' => $newValue));
                }else{
                    $status = $entityManager->getRepository(ReservationStatus::class)->findOneBy(array('name' => $newValue));
                }
                $reservation->SetStatus($status);
                if(strcmp($status->getName(), 'cancelled')===0){
                    $blockedRoomApi->deleteBlockedRoomByReservation($reservation->getId());
                }
                break;
            case "check_in_time":
                $reservation->SetCheckInTime($newValue);
                break;
            case "check_out_time":
                $reservation->SetCheckOutTime($newValue);
                break;
            case "check_in_status":

                if (strcmp($newValue, "checked_in") == 0) {
                    $logger->info("checked_in");
                    if ($reservationApi->isEligibleForCheckIn($reservation)) {
                        $reservation->setCheckInStatus($newValue);
                        $reservation->setCheckInTime($now->format("H:i"));
                        $notesApi->addNote($reservation->getId(), "Checked-in at " . $now->format("H:i"));
                    } else {
                        $responseArray[] = array(
                            'result_message' => "Please make sure the guest Id and phone number is captured",
                            'result_code' => 1
                        );
                        $logger->info(print_r($responseArray, true));
                        $callback = $request->get('callback');
                        $response = new JsonResponse($responseArray, 200, array());
                        $response->setCallback($callback);
                        return $response;
                    }

                } else if (strcmp($newValue, "checked_out") == 0) {
                    $logger->info("checked_out");
                    $due = $reservationApi->getAmountDue($reservation);
                    if ($due == 0) {
                        $reservation->setCheckInStatus($newValue);
                        $reservation->setCheckOutTime($now->format("H:i"));
                        $notesApi->addNote($reservation->getId(), "Checked-out at " . $now->format("H:i"));
                    } else {
                        $logger->info($due);
                        $responseArray[] = array(
                            'result_message' => "Please make sure the guest has settled their balance",
                            'result_code' => 1,
                            'due' => $due
                        );
                        $logger->info(print_r($responseArray, true));
                        $callback = $request->get('callback');
                        $response = new JsonResponse($responseArray, 200, array());
                        $response->setCallback($callback);
                        return $response;
                    }
                } else {
                    $responseArray[] = array(
                        'result_message' => "incorrect status provided",
                        'result_code' => 1
                    );
                    $logger->info(print_r($responseArray, true));
                    $callback = $request->get('callback');
                    $response = new JsonResponse($responseArray, 200, array());
                    $response->setCallback($callback);
                    return $response;
                }


                break;
            default:
                $responseArray[] = array(
                    'result_message' => "incorrect update field provided",
                    'result_code' => 1
                );
                $logger->info(print_r($responseArray, true));
                $callback = $request->get('callback');
                $response = new JsonResponse($responseArray, 200, array());
                $response->setCallback($callback);
                return $response;
        }
        $response = $reservationApi->updateReservation($reservation);
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/reservations/{reservationId}/update_checkin_time/{checkInTime}/{checkOutTime}")
     */
    public function updateReservationCheckInTime($reservationId, $checkInTime, $checkOutTime, Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, ReservationApi $reservationApi, BlockedRoomApi $blockedRoomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        $reservation = $reservationApi->getReservation($reservationId);

        $reservation->SetCheckInTime($checkInTime);
        $reservation->SetCheckOutTime($checkOutTime);

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
     * @Route("public/reservations/create/{roomIds}/{guestName}/{phoneNumber}/{adultGuests}/{childGuests}/{checkInDate}/{checkOutDate}/{email}", defaults={"email": ""})
     * @throws \Exception
     */
    public function creatReservation($roomIds, $guestName, $phoneNumber, $adultGuests, $childGuests, $checkInDate, $checkOutDate, $email, Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, ReservationApi $reservationApi, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $reservationApi->createReservation($roomIds, $guestName, $phoneNumber, $email, $checkInDate, $checkOutDate, $request, $adultGuests, $childGuests);
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
     * @Route("public/reviews/send/{propertyId}")
     * @throws \Exception
     */
    public function sendReviewRequest($propertyId, Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, ReservationApi $reservationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $reservationApi->sendReviewRequest($propertyId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("admin_api/reservations/{reservationId}/blockguest/{reason}")
     */
    public function blockGuest($reservationId, $reason, LoggerInterface $logger, Request $request,GuestApi $guestApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $guestApi->blockGuest($reservationId, $reason);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("admin_api/reservation_addon/{addOnId}/delete")
     */
    public function removeAddOnFromReservation($addOnId, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, AddOnsApi $addOnsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $addOnsApi->removeAddOnFromReservation($addOnId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/json/reservation/{id}")
     */
    public function getReservationJson( $id, LoggerInterface $logger, ReservationApi $api): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $reservation = $api->getReservation($id);

        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($reservation, 'json');

        $logger->info($jsonContent);
        return new JsonResponse($jsonContent , 200, array(), true);
    }

}