<?php

namespace App\Helpers\FormatHtml;

use App\Entity\Reservations;
use App\Entity\Guest;
use App\Service\AddOnsApi;
use App\Service\CleaningApi;
use App\Service\EmployeeApi;
use App\Service\GuestApi;
use App\Service\NotesApi;
use App\Service\PaymentApi;
use App\Service\RoomApi;
use DateTime;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;


class ReservationHtml
{

    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function formatHtml($reservations, $period, $propertyUid): string
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $htmlString = "";
        $now = new DateTime('today midnight');
        $tomorrow = new DateTime('tomorrow');
        $currentDate = $now->format("Y-m-d");
        $tomorrowHeadingWritten = false;
        $todayHeadingWritten = false;
        //if no reservations found
        if (count($reservations) < 1) {
            return '<div class="res-details">
						<h4 class="guest-name">No reservations found</h4>
					</div>';
        }

        $htmlString .= '<div class="flexible display-none" id="res_div_message_div_' . $period . '" >
										<div class="flex-bottom">
											<div class="flex1" id="res_div_success_message_div_' . $period . '">
												<h5 id="res_div_success_message_' . $period . '"></h5>
											</div>
											<div  class="flex2" id="res_div_error_message_div_' . $period . '">
												<h5 id="res_div_error_message_' . $period . '"></h5>
											</div>
										</div>
									</div>';


        $guestApi = new GuestApi($this->em, $this->logger);
        $addOnsApi = new AddOnsApi($this->em, $this->logger);
        $paymentApi = new PaymentApi($this->em, $this->logger);
        $notesApi = new NotesApi($this->em, $this->logger);
        $cleaningApi = new CleaningApi($this->em, $this->logger);
        $roomApi = new RoomApi($this->em, $this->logger);
        $rooms = $roomApi->getRoomsEntities($propertyUid);

        foreach ($reservations as $reservation) {
            //guest name and reservation ID
            $guest = $reservation->getGuest();
            $room = $reservation->getRoom();
            $reservationId = $reservation->getId();
            $addOns = $addOnsApi->getReservationAddOns($reservationId);

            //output tomorrow
            if (strcasecmp($period, "future") === 0) {
                if (!$todayHeadingWritten && (strcmp($reservation->getCheckIn()->format("Y-m-d"), $now->format("Y-m-d")) == 0)) {
                    $htmlString .= '<div class="res-details reservation-date-divider">
						<h4>Today - ' . $now->format("d M") . '</h4>
					</div>';

                    $todayHeadingWritten = true;
                }

                if ((strcmp($reservation->getCheckIn()->format("Y-m-d"), $tomorrow->format("Y-m-d")) == 0)
                    && !$tomorrowHeadingWritten) {
                    $htmlString .= '<div class="res-details reservation-date-divider">
						<h4>Tomorrow - ' . $tomorrow->format("d M") . '</h4>
					</div>';
                    $tomorrowHeadingWritten = true;
                }

                //output other dates
                if (strcmp($currentDate, $reservation->getCheckIn()->format("Y-m-d")) != 0
                    && $tomorrowHeadingWritten
                    && strcmp($reservation->getCheckIn()->format("Y-m-d"), $tomorrow->format("Y-m-d")) != 0) {
                    $currentDate = $reservation->getCheckIn()->format("Y-m-d");
                    $htmlString .= '<div class="res-details reservation-date-divider">
						<h4>' . $reservation->getCheckIn()->format("d M") . '</h4>
					</div>';
                }
            }


            //guest name and reservation id and count of stays
            $htmlString .= '<div class="res-details"><div class="left-div"><div class="inner-left-div">


						<h4 class="guest-name"><div class="stays-div">' . $guestApi->getGuestStaysCount($guest->getId())[0]["result_message"] . '</div><a target="_blank" href="/invoice.html?id=' . $reservationId . '">' . $guest->getName() . ' - ' . $reservationId . '</a>';

            //is short stay?
            if (strcmp($reservation->getCheckIn()->format("Y-m-d"), $reservation->getCheckOut()->format("Y-m-d")) == 0) {
                $htmlString .= '<img src="images/clock.ico" class="icon-small-image" title="Short Stay"/>';
            }


            //reservation origin
            $this->logger->debug("HTML output - reservation origin " . $reservation->getId());

            $htmlString .= '<img title="' . $reservation->getOrigin() . '" src="images/' . $reservation->getOrigin() . '.png" class="icon-small-image"></img>';

            $htmlString .= '</h4>';

            //room name
            $roomDisabled = "";
            if (strcasecmp($period, "past") === 0
                || strcasecmp($period, "stayover") === 0
                || strcasecmp($period, "checkout") === 0) {
                $roomDisabled = "Disabled";
            }

            $htmlString .= '<p name="res-dates"><span class="glyphicon glyphicon-home glyphicon-small-icon" > 
<select id"select_room_' . $reservationId . '" data-res-id="' . $reservationId . '" class="reservation_room_input" ' . $roomDisabled . '>';
            foreach ($rooms as $roomEntity) {
                if ($roomEntity->getId() === $room->getId()) {
                    $htmlString .= '<option value="' . $roomEntity->getId() . '" selected>' . $roomEntity->getName() . '</option>';
                } else {
                    $htmlString .= '<option value="' . $roomEntity->getId() . '" >' . $roomEntity->getName() . '</option>';
                }
            }
            $htmlString .= '</select></p>';

            //check in\out date

            $checkInDateDisabled = "";
            if (strcasecmp($period, "past") === 0) {
                $checkInDateDisabled = "Disabled";
            }

            $htmlString .= '<p name="res-dates"><span class="glyphicon glyphicon-calendar glyphicon-small-icon" > 
						 <input id="checkindate_' . $reservationId . '" data-res-id="' . $reservationId . '" type="text"  name="check_in_date" class="input-as-text date-picker check_in_date_input" value="' . $reservation->getCheckIn()->format("m/d/Y") .
                ' - ' . $reservation->getCheckOut()->format("m/d/Y") . '" ' . $checkInDateDisabled . '/>
						 </span></p>';

            //check in time
            //disable check in time for some reservations
            $checkInTimeDisabled = "";
            if (strcasecmp($period, "past") === 0
                || strcasecmp($period, "stayover") === 0
                || strcasecmp($period, "checkout") === 0) {
                $checkInTimeDisabled = "Disabled";
            }

            //disable check out time for some reservations
            $checkOutTimeDisabled = "";
            if (strcasecmp($period, "past") === 0) {
                $checkInTimeDisabled = "Disabled";
            }

            //check if is early check in between 11h00 and 13h00

            $isEarlyCheckInClass = "";
            if (str_contains($reservation->getCheckInTime(), '08')
                || str_contains($reservation->getCheckInTime(), '09')
                || str_contains($reservation->getCheckInTime(), '10')
                || str_contains($reservation->getCheckInTime(), '11')
                || str_contains($reservation->getCheckInTime(), '12')
                || str_contains($reservation->getCheckInTime(), '13')
            ) {
                $isEarlyCheckInClass = "early-check-in";
            }

            $htmlString .= '<span class="glyphicon glyphicon-time glyphicon-small-icon" >  
<input data-res-id="' . $reservationId . '" type="text"  name="check_in_time" class="input-as-text time-picker check_in_time_input ' . $isEarlyCheckInClass . '" value="' . $reservation->getCheckInTime() . '" ' . $checkInTimeDisabled . '> - 
<input data-res-id="' . $reservationId . '" type="text" name="check_out_time" class="input-as-text  time-picker check_out_time_input" value="' . $reservation->getCheckOutTime() . '" ' . $checkOutTimeDisabled . '></span>';
            //contact detailsh

            $this->logger->debug("HTML output - contact details " . $reservation->getId());
            if ($guest->getPhoneNumber() == Null) {
                $htmlString .= '';
                $htmlString .= '<p name="guest-contact"><span class="glyphicon glyphicon-earphone glyphicon-small-icon" ><input name="phone_number" type="text" customer_id="' . $guest->getId() . '"   
							placeholder="Phone Number" class="textbox phone_number_input"></span></p>';

            } else {
                $htmlString .= '<p name="guest-contact"><span class="glyphicon glyphicon-earphone glyphicon-small-icon" ><a href="tel:' . $guest->getPhoneNumber() . '">  ' . $guest->getPhoneNumber() . '</a></span></p>';
            }

            // check if room cleaned for checkout reservations only
            $this->logger->debug("HTML output - check if room cleaned for checkout reservations only " . $reservation->getId());
            $results = $cleaningApi->isRoomCleanedForCheckOut($reservationId);
            if ($results[0]['cleaned']) {
                $cleanedBy = $results[0]['cleaned_by'];
                $htmlString .= '<p><span class="em1-right-margin glyphicon glyphicon-certificate" ></span>Room Cleaned By ' . $cleanedBy . '</p>';
            }


            //notes
            $this->logger->debug("HTML output - notes " . $reservation->getId());
            $notes = $notesApi->getReservationNotes($reservationId);
            if (count($notes) > 0) {
                $htmlString .= '<h5 class="text-align-left">Notes</h5>';
                foreach ($notes as $note) {
                    $htmlString .= "<p>" . $note->getDate()->format("d-M") . " - " . $note->getNote() . "</p>";
                }
            }

            //customer image
            $this->logger->debug("HTML output - customer image" . $reservation->getId());
            if ($guest->getIdNumber() == null) {
                $customerIdImage = "unverified.png";
            } else {
                $customerIdImage = "verified.png";
            }

            //far left bottom

            $htmlString .= '<p class="far-left">';
            //for direct bookings only
            $this->logger->debug("HTML output - for direct bookings only " . $reservation->getId());
            if (strcasecmp($reservation->getOrigin(), "website") == 0 && strcasecmp($period, "past") != 0) {

                if (strcmp($reservation->getStatus()->getName(), "pending") != 0) {
                    //cancel booking
                    $this->logger->debug(" HTML output - cancel booking " . $reservation->getId());
                    $htmlString .= '<span title="Cancel booking" class="glyphicon glyphicon-remove changeBookingStatus clickable" aria-hidden="true" id="cancelBooking_' . $reservationId . '"></span>';
                    /*                    //open close room
                                        $this->logger->debug(" HTML output - open close room " . $reservation->getId());
                                        if (strcasecmp($reservation->getStatus()->getName(), "confirmed") == 0) {
                                            $htmlString .= '<span title="Open\Close Room" class="glyphicon glyphicon-triangle-top changeBookingStatus clickable" aria-hidden="true" id="changeBookingStatus_' . $reservationId . '"></span>';
                                        } else {
                                            $htmlString .= '<span title="Open\Close Room" class="glyphicon glyphicon-triangle-bottom changeBookingStatus clickable" aria-hidden="true" id="changeBookingStatus_' . $reservationId . '"></span>';
                                        }*/
                }
            }

            //whatsapp guest
            $this->logger->debug(" HTML output - whatsapp guest " . $reservation->getId());
            $htmlString .= '<a title="Whatsapp Guest" class="image_verified" target="_blank" href="https://api.whatsapp.com/send?phone=' . $guest->getPhoneNumber() . '&text=Hello)"><i class="fa fa-whatsapp" aria-hidden="true"></i></a>';

            //guest id verified
            if (strcasecmp($reservation->getOrigin(), "website") === 0) {
                $htmlString .= '<img title="Customer ID - ' . str_replace(".png", "", $customerIdImage) . '" src="images/' . $customerIdImage . '" class="image_verified clickable"/>';
            }

            // display if guest already checked in
            $this->logger->debug("HTML output - display if guest already checked in" . $reservation->getId());
            if (strcasecmp($reservation->getCheckInStatus(), "checked_in") == 0) {
                $htmlString .= '<img src="images/menu_stayover.png" title="Checked in" class="image_verified"/><p></p>';
            }

            //display if guest checked out
            $this->logger->debug("HTML output - display if guest already checked in" . $reservation->getId());
            if (strcasecmp($reservation->getCheckInStatus(), "checked_out") == 0 &&
                (strcmp($reservation->getCheckOut()->format("Y-m-d"), $now->format("Y-m-d") == 0))) {
                $htmlString .= '<img src="images/checked_out.png" title="Checked out" class="image_verified"/><p></p>';
            }

            //booking created on
            $this->logger->debug("HTML output - bottom right icons " . $reservation->getId());
            $htmlString .= '<p> Received on: ' . $reservation->getReceivedOn()->format('Y-m-d') . '</p>';
            $htmlString .= '<p><a href="javascript:void(0)" class="reservations_actions_link" data-res-id="' . $reservation->getId() . '">more...</a></p>';
            //close far right
            $htmlString .= '</p>';


            //close bottom icon section and other divs
            $this->logger->debug(" HTML output - close bottom icon section and other divs " . $reservation->getId());
            $htmlString .= '</p>   

						<div class="clearfix"><div></div></div></div>';


            //inner right div
            $htmlString .= '<div class="inner-right-div">';


            //Line Items (Room and add ons)
            $this->logger->debug("HTML output - Line Items " . $reservation->getId());
            $roomPrice = 0;
            if (strcasecmp($reservation->getOrigin(), "website") == 0) {
                $roomPrice = $room->getPrice();
            }

            $totalDays = intval($reservation->getCheckIn()->diff($reservation->getCheckOut())->format('%a'));

            $this->logger->debug("total days is " . $totalDays . $reservation->getCheckIn()->format("Y-m-d") . " - " . $reservation->getCheckOut()->format("Y-m-d"));

            $this->logger->debug("looping add ons for reservation " . $reservationId . " add on count " . count($addOns));
            $totalPriceForAllAdOns = 0;
            $addOnsHTml = "";
            foreach ($addOns as $addOn) {
                $addOnsHTml .= '<p class="small-font-italic">' . $addOn->getDate()->format("d-M") . " - " . $addOn->getQuantity() . " x " . $addOn->getAddOn()->getName() . " @ R " . $addOn->getAddOn()->getPrice() . '</p>';
                $totalPriceForAllAdOns += (intVal($addOn->getAddOn()->getPrice()) * intval($addOn->getQuantity()));
            }
            $totalPrice = intval($roomPrice) * $totalDays;
            $totalPrice += $totalPriceForAllAdOns;

            //payments
            $this->logger->debug("HTML output - payments " . $reservation->getId());
            $payments = $paymentApi->getReservationPayments($reservationId);
            $paymentsHtml = "";
            $totalPayment = 0;
            foreach ($payments as $payment) {
                $paymentsHtml .= '<p class="small-font-italic"> ' . $payment->getDate()->format("d-M") . ' - R' . number_format((float)$payment->getAmount(), 2, '.', '') . '</p>';
                $totalPayment += (intVal($payment->getAmount()));
            }

            $due = $totalPrice - $totalPayment;

            if ($totalPrice > 0) {
                $htmlString .= '<h5 class="text-align-left">Line items</h5>';

                if ((strcmp($reservation->getCheckIn()->format("Y-m-d"), $reservation->getCheckOut()->format("Y-m-d")) != 0
                    && strcasecmp($reservation->getOrigin(), "website") === 0)) {
                    $htmlString .= '<p class="small-font-italic">' . $totalDays . "x nights @ R" . $roomPrice . "</p>";
                }

                $htmlString .= $addOnsHTml;
                $htmlString .= '<h5 class="text-align-left">Total: R' . number_format((float)$totalPrice, 2, '.', '') . '</h5>';
                $htmlString .= '<h5 class="text-align-left">Paid: R' . number_format((float)$totalPayment, 2, '.', '') . '</h5>';
                $htmlString .= $paymentsHtml;
                $htmlString .= '<h5 class="text-align-left">Due R' . number_format((float)$due, 2, '.', '') . '</h5>';

            }

            //cleanings
            $this->logger->debug("HTML output - Cleanings list " . $reservation->getId());
            $cleanings = $cleaningApi->getReservationCleanings($reservationId);
            if (count($cleanings)) {
                $htmlString .= '<h5 class="text-align-left">Cleanings</h5>';
                foreach ($cleanings as $cleaning) {
                    $htmlString .= '<p class="small-font-italic"> ' . $cleaning->getDate()->format("d-M") . ' - ' . $cleaning->getCleaner()->getName() . '</p>';
                }
            }


            //close right inner div
            $htmlString .= '</div></div>';

            //right div for input fields
            $this->logger->debug(" HTML output - right div for input fields " . $reservation->getId());

            $htmlString .= '<div class="right-div" id="right-div-' . $reservation->getId() . '">';

            // add Guest ID
            if ($guest->getIdNumber() == null) {
                $this->logger->debug(" HTML output - add guest ID" . $reservation->getId());

                $htmlString .= '
                <div class="right-side-action-block">
                <input id="guest_id_' . $reservationId . '" type="text"
										 class="textbox  display-none block-display reservation_input" placeholder="Passport\ID number"/><div id="add_guest_id_button_' . $reservationId . '" class="ClickableButton res_add_guest_id" data-resid="' . $reservationId . '" >Add ID\Passport</div></div>';
            }

            // add payment
            $this->logger->debug(" HTML output - add payment" . $reservation->getId());

            $htmlString .= '
                <div class="right-side-action-block">
                <input id="amount_' . $reservationId . '" type="text"
										 class="textbox  display-none block-display reservation_input" placeholder="0.00"/><div id="add_payment_button_' . $reservationId . '" class="ClickableButton res_add_payment" data-resid="' . $reservationId . '" >Add Payment</div></div>';

            // add notes
            $this->logger->debug(" HTML output - add notes" . $reservation->getId());
            $htmlString .= '
                    <div class="right-side-action-block">
                    <textarea id="note_' . $reservationId . '"
                        class="textbox  display-none block-display reservation_input" placeholder="e.g. 12h00 early check in"/><div id="add_note_button_' . $reservationId . '" class="ClickableButton res_add_note" data-resid="' . $reservationId . '" >Add Note</div></div>';

            // add add-ons - only for confirmed booking
            if (strcmp($reservation->getStatus()->getName(), "pending") != 0) {
                $this->logger->debug(" HTML output - add add-ons" . $reservation->getId());
                $htmlString .= ' <div class="right-side-action-block"><div class="display-none borderAndPading block-display reservation_input" id="div_add_on_' . $reservationId . '" ><select id="select_add_on_' . $reservationId . '">';
                $htmlString .= ' <option value="none">Select Add On</option>';
                $addOnsList = $addOnsApi->getAddOns($propertyUid);
                if ($addOnsList !== null) {
                    foreach ($addOnsList as $addOn) {
                        $htmlString .= ' <option value="' . $addOn->getId() . '">' . $addOn->getName() . '</option>';
                    }
                }

                $htmlString .= '</select> 
            <p>Quantity</p><input type="number" value="1" id="add_on_quantity_' . $reservationId . '"></p>
            </div>';

                $htmlString .= '
                    <div id="add_add_on_button_' . $reservationId . '" class="ClickableButton res_add_add_on" data-resid="' . $reservationId . '" >Add Add-Ons</div></div>';

            }

            // check if guest eligible for check in 1. Guest ID provided 2. guest has phone number recorded
            $this->logger->debug("HTML output - check if guest eligible for check in" . $reservation->getId());

            if (strcasecmp($reservation->getCheckInStatus(), "not_checked_in") === 0
                && (strcasecmp($reservation->getCheckIn()->format("Y-m-d"), $now->format("Y-m-d")) == 0)) {
                $this->logger->debug("this user is checking in today");
                $htmlString .= '<div class="right-side-action-block"><div class="NotCheckedIn" id="check_in_user_' . $reservationId . '"  reservation_id="' . $reservationId . '">Check In Guest</div></div>';
            }

            // guest checkout button
            $this->logger->debug("HTML output - check if guest eligible for check in" . $reservation->getId());
            if (strcasecmp($reservation->getCheckInStatus(), "checked in") === 0 && (strcasecmp($reservation->getCheckOut()->format("Y-m-d"), $now->format("Y-m-d")) == 0)) {
                $htmlString .= '<div class="right-side-action-block"><div class="NotCheckedOut" id="check_out_user_' . $reservationId . '" reservation_id="' . $reservationId . '">Check Out Guest</div></div>';
            }

            //Mark room as cleaned
            $this->logger->debug("HTML output - Mark room as cleaned " . $reservation->getId());
            $htmlString .= ' <div class="right-side-action-block"><div class="display-none borderAndPading block-display reservation_input" id="div_mark_cleaned_' . $reservationId . '" ><select id="select_employee_' . $reservationId . '">';
            $htmlString .= ' <option value="none">Select Cleaner</option>';
            $employeeApi = new EmployeeApi($this->em, $this->logger);
            $employees = $employeeApi->getEmployees($propertyUid);
            if (count($employees) > 0) {
                foreach ($employees as $employee) {
                    $htmlString .= ' <option value="' . $employee->getId() . '">' . $employee->getName() . '</option>';
                }
            }

            $htmlString .= '</select> 
            </div>';

            $htmlString .= '
                    <div id="mark_cleaned_button_' . $reservationId . '" class="ClickableButton res_mark_cleaned" data-resid="' . $reservationId . '" >Add Cleaning</div></div>';


            //cleaning score - if check out date is in the past and not direct booking
            $this->logger->debug("HTML output - cleaning score " . $reservation->getId());
            if ($reservation->getCheckOut() < $now && strcasecmp($reservation->getOrigin(), "website") != 0) {
                //check if cleanliness score is not captured
                if (strcasecmp($reservation->getCleanlinessScore(), "0") == 0) {
                    //display the input field to capture score
                    $htmlString .= '<input name="score" type="text" id="score_input_' . $reservationId . '"   
							placeholder="Score" class="textbox cleaning_score_input display-none">';
                    $htmlString .= '<div class="ClickableButton res_add_add_on" reservation_id="' . $reservationId . '">Cleanliness Score</div>';
                } else {
                    //display score
                    $htmlString .= '<div>Cleanliness score: ' . $reservation->getCleanlinessScore() . '</div>';
                }
            }

            $this->logger->debug("Ending HTML output" . $reservation->getId());
            $htmlString .= '</div>
					</div>';
        }
        return $htmlString;
    }
}