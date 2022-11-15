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


class SingleReservationHtml
{

    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function formatHtml($reservation): string
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $htmlString = "";
        $now = new DateTime('today midnight');


        if ($reservation === null) {
            return '<div class="reservation-date-divider">
                            No reservations found
                        </div>';
        }

        $htmlString .= '
<div class="reservation-date-divider">
                            Reservation Details
                        </div>
                        <div class="flexible display-none" id="res_div_message_div" >
										<div class="flex-bottom">
											<div class="flex1" id="res_div_success_message_div">
												<h5 id="res_div_success_message"></h5>
											</div>
											<div  class="flex2" id="res_div_error_message_div">
												<h5 id="res_div_error_message"></h5>
											</div>
										</div>
									</div>';


        $guestApi = new GuestApi($this->em, $this->logger);
        $addOnsApi = new AddOnsApi($this->em, $this->logger);
        $paymentApi = new PaymentApi($this->em, $this->logger);
        $notesApi = new NotesApi($this->em, $this->logger);
        $cleaningApi = new CleaningApi($this->em, $this->logger);
        $roomApi = new RoomApi($this->em, $this->logger);
        $rooms = $roomApi->getRoomsEntities();

        //guest name and reservation ID
        $guest = $reservation->getGuest();
        $room = $reservation->getRoom();
        $reservationId = $reservation->getId();
        $addOns = $addOnsApi->getReservationAddOns($reservationId);

        //guest name and reservation id and count of stays
        $htmlString .= '<div class="res-details"><div><div>
						<h4 class="guest-name"><a target="_blank" href="http://'.$room->getProperty()->getServerName().'/invoice.html?id=' . $reservationId . '">' . $guest->getName() . ' - ' . $reservationId . '</a>';

        //is short stay?
        if (strcmp($reservation->getCheckIn()->format("Y-m-d"), $reservation->getCheckOut()->format("Y-m-d")) == 0) {
            $htmlString .= '<img src="/admin/images/clock.ico" class="icon-small-image" title="Short Stay"/>';
        }


        //reservation origin
        $this->logger->debug("HTML output - reservation origin " . $reservation->getId());

        $htmlString .= '<img title="' . $reservation->getOrigin() . '" src="/admin/images/' . $reservation->getOrigin() . '.png" class="icon-small-image"></img>';

        $htmlString .= '</h4>';

        //room name
        $roomDisabled = "";
        if ($reservation->getCheckIn() < $now || (strcasecmp($reservation->getOrigin(), "website") !=0) ){
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
        if ($reservation->getCheckOut() < $now || (strcasecmp($reservation->getOrigin(), "website") !=0)){
            $checkInDateDisabled = "Disabled";
        }

        $htmlString .= '<p name="res-dates"><span class="glyphicon glyphicon-calendar glyphicon-small-icon" > 
						 <input id="checkindate_' . $reservationId . '" data-res-id="' . $reservationId . '" type="text"  name="check_in_date" class="input-as-text date-picker check_in_date_input" value="' . $reservation->getCheckIn()->format("m/d/Y") .
            ' - ' . $reservation->getCheckOut()->format("m/d/Y") . '" ' . $checkInDateDisabled . '/>
						 </span></p>';

        //check in time
        //disable check in time for some reservations
        $checkInTimeDisabled = "";
        $checkOutTimeDisabled = "";
        if ($reservation->getCheckIn() < $now || (strcasecmp($reservation->getOrigin(), "website") !=0) ){
            $checkInTimeDisabled = "Disabled";
            $checkOutTimeDisabled = "Disabled";
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

        /*$htmlString .= '<span class="glyphicon glyphicon-time glyphicon-small-icon" >
<input data-res-id="' . $reservationId . '" type="text"  name="check_in_time" class="input-as-text time-picker check_in_time_input ' . $isEarlyCheckInClass . '" value="' . $reservation->getCheckInTime() . '" ' . $checkInTimeDisabled . '> - 
<input data-res-id="' . $reservationId . '" type="text" name="check_out_time" class="input-as-text  time-picker check_out_time_input" value="' . $reservation->getCheckOutTime() . '" ' . $checkOutTimeDisabled . '></span>';
        */


        //contact details

        $this->logger->debug("HTML output - contact details " . $reservation->getId());
        if ($guest->getPhoneNumber() !== Null &&  !empty($guest->getPhoneNumber())) {
            $htmlString .= '<p name="guest-contact" class="guest-contact"><span class="glyphicon glyphicon-earphone glyphicon-small-icon" ><a class="res-contact-link" href="tel:' . $guest->getPhoneNumber() . '">  ' . $guest->getPhoneNumber() . '</a></span></p>';
        }

        if ($guest->getEmail() !== Null && !empty($guest->getEmail())) {
            $htmlString .= '<p name="guest-contact" class="guest-contact"><span class="glyphicon glyphicon-envelope glyphicon-small-icon"><a class="res-contact-link" href="mailto:' . $guest->getEmail() . '">  ' . $guest->getEmail() . '</a></span></p>';
        }

        if ($reservation->getAdults() !== Null && $reservation->getChildren() !== Null) {
            $htmlString .= '<p name="guest-contact" class="guest-contact"><span class="glyphicon glyphicon-user glyphicon-small-icon"><a class="res-contact-link" href="javascript:void(0)">'.$reservation->getAdults().' Adults and ' . $reservation->getChildren() . ' Children</a></span></p>';
        }

        if ($guest->getIdNumber() !== Null && !empty($guest->getIdNumber())) {
            $htmlString .= '<p name="guest-contact" class="guest-contact"><span class="glyphicon glyphicon-user glyphicon-small-icon"><a class="res-contact-link" href="javascript:void(0)">'. $guest->getIdNumber() . '</a></span></p>';
        }

        // check if room cleaned for checkout reservations only
        $this->logger->debug("HTML output - check if room cleaned for checkout reservations only " . $reservation->getId());
        $results = $cleaningApi->isRoomCleanedForCheckOut($reservationId);
        if ($results[0]['cleaned']) {
            $cleanedBy = $results[0]['cleaned_by'];
            $htmlString .= '<p><span class="em1-right-margin glyphicon glyphicon-certificate" ></span>Room Cleaned By ' . $cleanedBy . '</p>';
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

        if ($reservation->getCheckIn() >= $now) {

            if (strcmp($reservation->getStatus()->getName(), "pending") != 0) {
                //cancel booking
                $this->logger->debug(" HTML output - cancel booking " . $reservation->getId());
                $htmlString .= '<span title="Cancel booking" class="glyphicon glyphicon-remove changeBookingStatus clickable" aria-hidden="true" id="cancelBooking_' . $reservationId . '"></span>';
            }
        }

        //whatsapp guest
        $this->logger->debug(" HTML output - whatsapp guest " . $reservation->getId());
        $htmlString .= '<a title="Whatsapp Guest" class="image_verified" target="_blank" href="https://api.whatsapp.com/send?phone=' . $guest->getPhoneNumber() . '&text=Hello)"><i class="fa fa-whatsapp" aria-hidden="true"></i></a>';

        //guest id verified
        if (strcasecmp($reservation->getOrigin(), "website") === 0) {
            $htmlString .= '<img title="Customer ID - ' . str_replace(".png", "", $customerIdImage) . '" src="/admin/images/' . $customerIdImage . '" class="image_verified clickable"/>';
        }

        // display if guest already checked in
        $this->logger->debug("HTML output - display if guest already checked in" . $reservation->getId());
        if (strcasecmp($reservation->getCheckInStatus(), "checked_in") == 0) {
            $htmlString .= '<img src="/admin/images/menu_stayover.png" title="Checked in" class="image_verified"/><p></p>';
        }

        //display if guest checked out
        $this->logger->debug("HTML output - display if guest already checked in" . $reservation->getId());
        if (strcasecmp($reservation->getCheckInStatus(), "checked_out") == 0 &&
            (strcmp($reservation->getCheckOut()->format("Y-m-d"), $now->format("Y-m-d") == 0))) {
            $htmlString .= '<img src="/admin/images/checked_out.png" title="Checked out" class="image_verified"/><p></p>';
        }

        //booking created on
        $this->logger->debug("HTML output - bottom right icons " . $reservation->getId());
        $htmlString .= '<p class="top-margin-1em"> Received on: ' . $reservation->getReceivedOn()->format('Y-m-d') . '</p>';

        $htmlString .= '<hr>';

        $htmlString .= '</p>';


        //close bottom icon section and other divs
        $this->logger->debug(" HTML output - close bottom icon section and other divs " . $reservation->getId());
        $htmlString .= '</p>   

						<div class="clearfix"><div></div></div></div>';


        //items
        $htmlString .= '<div>';


        //notes
        $this->logger->debug("HTML output - notes " . $reservation->getId());
        $notes = $notesApi->getReservationNotes($reservationId);
        if (count($notes) > 0) {
            $htmlString .= '<h5 class="text-align-left">Notes</h5>';
            foreach ($notes as $note) {
                $htmlString .= "<p>" . $note->getDate()->format("d-M") . " - " . $note->getNote() . "</p>";
            }
        }

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
            if($payment->isDiscount()){
                $paymentsHtml .= '<p class="small-font-italic"> ' . $payment->getDate()->format("d-M") . ' - R' . number_format((float)$payment->getAmount(), 2, '.', '') . ' (Discount)</p>';
            }else{
                $paymentsHtml .= '<p class="small-font-italic"> ' . $payment->getDate()->format("d-M") . ' - R' . number_format((float)$payment->getAmount(), 2, '.', '') . '</p>';
            }
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

        $htmlString .= '<div id="right-div-' . $reservation->getId() . '">';

        // add phone number
        if ($guest->getPhoneNumber() === null || empty($guest->getPhoneNumber())) {
            $this->logger->debug(" HTML output - add guest phonenumber" . $reservation->getId());

            $htmlString .= '
                <div class="right-side-action-block">
                <input id="guest_phone_input" type="text" data-guestid="' . $guest->getId() . '"
										 class="textbox  display-none block-display reservation_input" placeholder="Phone number"/><div id="add_guest_phone_button" class="ClickableButton res_add_guest_phone" data-guestid="' . $guest->getId() . '" >Add Phone Number</div></div>';
        }

        // add email
        if ($guest->getEmail() === null || empty($guest->getPhoneNumber())) {
            $this->logger->debug(" HTML output - add guest email" . $reservation->getId());

            $htmlString .= '
                <div class="right-side-action-block">
                <input id="guest_email_input" type="text" data-guestid="' . $guest->getId() . '"
										 class="textbox  display-none block-display reservation_input" placeholder="Email Address"/><div id="add_guest_email_button" class="ClickableButton res_add_guest_email" data-guestid="' . $guest->getId() . '" >Add Email</div></div>';
        }

        // add Guest ID
        if ($guest->getIdNumber() === null || empty($guest->getIdNumber())) {
            $this->logger->debug(" HTML output - add guest ID" . $reservation->getId());

            $htmlString .= '
                <div class="right-side-action-block">
                <input id="guest_id_input" type="text" data-guestid="' . $guest->getId() . '"
										 class="textbox  display-none block-display reservation_input" placeholder="Passport\ID number"/><div id="add_guest_id_button" class="ClickableButton res_add_guest_id" data-guestid="' . $guest->getId() . '" >Add ID\Passport</div></div>';
        }

        // add payment
        $this->logger->debug(" HTML output - add payment" . $reservation->getId());


        $htmlString .= ' <div class="right-side-action-block"><div class="display-none borderAndPading block-display reservation_input" id="div_payment" >
        <select id="select_payment_' . $reservationId . '">';
        $htmlString .= ' <option value="none">Select Payment Method</option>';
        $htmlString .= ' <option value="cash">Cash</option>';
        $htmlString .= ' <option value="card">Card</option>';
        $htmlString .= ' <option value="transfer">Transfer</option>';

        $htmlString .= '</select> 
            <p>Amount</p><input id="amount_' . $reservationId . '" type="text"
										 class="textbox  display-none block-display reservation_input" placeholder="0.00"/></p>
            </div>';

        $htmlString .= '
                    <div id="add_payment_button_' . $reservationId . '" class="ClickableButton res_add_payment" data-resid="' . $reservationId . '" >Add Payment</div></div>';


        // add discount
        $this->logger->debug(" HTML output - add discount" . $reservation->getId());

        $htmlString .= '
                <div class="right-side-action-block">
                <input id="discount_' . $reservationId . '" type="text"
										 class="textbox  display-none block-display reservation_input" placeholder="0.00"/><div id="add_discount_button_' . $reservationId . '" class="ClickableButton res_add_discount" data-resid="' . $reservationId . '" >Add Discount</div></div>';

        // add notes
        $this->logger->debug(" HTML output - add notes" . $reservation->getId());
        $htmlString .= '
                    <div class="right-side-action-block">
                    <textarea id="note_' . $reservationId . '"
                        class="textbox  display-none block-display reservation_input" placeholder="e.g. 12h00 early check in"></textarea><div id="add_note_button_' . $reservationId . '" class="ClickableButton res_add_note" data-resid="' . $reservationId . '" >Add Note</div></div>';

        // add add-ons - only for confirmed booking
        if (strcmp($reservation->getStatus()->getName(), "pending") != 0) {
            $this->logger->debug(" HTML output - add add-ons" . $reservation->getId());
            $htmlString .= ' <div class="right-side-action-block"><div class="display-none borderAndPading block-display reservation_input" id="div_add_on_' . $reservationId . '" ><select id="select_add_on_' . $reservationId . '">';
            $htmlString .= ' <option value="none">Select Add On</option>';
            $addOnsList = $addOnsApi->getAddOns();
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

        if (strcasecmp($reservation->getCheckInStatus(), "not checked in") === 0
            && (strcasecmp($reservation->getCheckIn()->format("Y-m-d"), $now->format("Y-m-d")) == 0)) {
            $this->logger->debug("this user is checking in today");
            $htmlString .= '<div class="right-side-action-block"><div class="ClickableButton NotCheckedIn" id="check_in_user_' . $reservationId . '"  reservation_id="' . $reservationId . '">Check In Guest</div></div>';
        }

        // guest checkout button
        $this->logger->debug("HTML output - check if guest eligible for check out" . $reservation->getId());
        if (strcasecmp($reservation->getCheckInStatus(), "checked in") === 0 && (strcasecmp($reservation->getCheckOut()->format("Y-m-d"), $now->format("Y-m-d")) == 0)) {
            $htmlString .= '<div class="right-side-action-block"><div class="ClickableButton NotCheckedOut" id="check_out_user_' . $reservationId . '" reservation_id="' . $reservationId . '">Check Out Guest</div></div>';
        }

        //Mark room as cleaned
        $this->logger->debug("HTML output - Mark room as cleaned " . $reservation->getId());
        $htmlString .= ' <div class="right-side-action-block"><div class="display-none borderAndPading block-display reservation_input" id="div_mark_cleaned_' . $reservationId . '" ><select id="select_employee_' . $reservationId . '">';
        $htmlString .= ' <option value="none">Select Cleaner</option>';
        $employeeApi = new EmployeeApi($this->em, $this->logger);
        $employees = $employeeApi->getEmployees();
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

        return $htmlString;
    }
}