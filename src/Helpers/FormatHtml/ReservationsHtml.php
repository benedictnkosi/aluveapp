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
use DateInterval;
use DateTime;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;


class ReservationsHtml
{

    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function formatHtml($reservations, $period): string
    {

        $this->logger->debug("Starting Method: " . __METHOD__);
        $this->createCSV($reservations, $period);
        $this->createFlatFile($reservations, $period);

        $htmlString = "";
        $todayHeadingWritten = false;
        //if no reservations found

        if ($reservations === null) {
            return '<div class="reservation-item">
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
									</div>
									
									<a href="/'.$period . '_reservations.csv" >Download CSV</a>
<a href="/'.$period . '_reservations.txt" >| Download Flat File</a>
									';

        if (strcmp($period, 'past') === 0) {
            $numberOfDays = 180;
            for ($x = 0; $x <= $numberOfDays; $x++) {
                $todayDate = new DateTime();
                $todayDate->add(DateInterval::createFromDateString('yesterday'));
                $tempDate = $todayDate->sub(new DateInterval('P' . $x . 'D'));
                $htmlString .= $this->helper($tempDate, $reservations);
            }
        } else {
            $numberOfDays = 180;
            for ($x = 1; $x <= $numberOfDays; $x++) {
                $todayDate = new DateTime();
                $todayDate->add(DateInterval::createFromDateString('yesterday'));
                $tempDate = $todayDate->add(new DateInterval('P' . $x . 'D'));
                if (strcmp($period, 'pending') === 0) {
                    $htmlString .= $this->helper($tempDate, $reservations, false);
                } else {
                    $htmlString .= $this->helper($tempDate, $reservations);
                }

            }
        }


        return $htmlString;
    }


    function helper($tempDate, $reservations, $outputCheckOuts = true): string
    {

        $todayCheckIns = array();
        $todayCheckOuts = array();
        $htmlString = '';

        foreach ($reservations as $reservation) {

            if (strcmp($reservation->getCheckIn()->format("Y-m-d"), $tempDate->format("Y-m-d")) == 0) {
                $todayCheckIns[] = ($reservation);
            }

            if (strcmp($reservation->getCheckOut()->format("Y-m-d"), $tempDate->format("Y-m-d")) == 0) {
                $todayCheckOuts[] = ($reservation);
            }
        }

        if (!empty($todayCheckIns) || (!empty($todayCheckOuts) && $outputCheckOuts)) {
            $htmlString .= '<div class="reservation-date-divider">
                            ' . $tempDate->format("d M") . '
                        </div>';
        }

        foreach ($todayCheckIns as $todayCheckIn) {
            $htmlString .= '<div class="reservation-item" data-res-id="' . $todayCheckIn->getId() . '">
                         <div class="listing-description clickable open-reservation-details" data-res-id="' . $todayCheckIn->getId() . '">
                          <img class="listing-checkin-image listing-image" src="/admin/images/listing-checkin.png" data-res-id="' . $todayCheckIn->getId() . '"></img>
                        <img class="listing-image-origin" src="/admin/images/' . $todayCheckIn->getOrigin() . '.png" data-res-id="' . $todayCheckIn->getId() . '"></img>
                        <div class="listing-description-text" data-res-id="' . $todayCheckIn->getId() . '">'
                . $todayCheckIn->getGuest()->getName() . ' is expected to check-in 
                         <span class="listing-room-name" data-res-id="' . $todayCheckIn->getId() . '"> ' . $todayCheckIn->getRoom()->getName() . ' #' . $todayCheckIn->getId() . '</span>
                        </div>
                        </div>
                    </div>';
        }

        if ($outputCheckOuts) {
            foreach ($todayCheckOuts as $todayCheckOut) {
                $htmlString .= '<div class="reservation-item" data-res-id="' . $todayCheckOut->getId() . '">
                         <div class="listing-description clickable open-reservation-details" data-res-id="' . $todayCheckOut->getId() . '">
                          <img class="listing-checkin-image listing-image" src="/admin/images/listing-checkout.png" data-res-id="' . $todayCheckOut->getId() . '"></img>
                        <img class="listing-image-origin" src="/admin/images/' . $todayCheckOut->getOrigin() . '.png" data-res-id="' . $todayCheckOut->getId() . '"></img>
                        <div class="listing-description-text" data-res-id="' . $todayCheckOut->getId() . '">'
                    . $todayCheckOut->getGuest()->getName() . ' is expected to check-out 
                         <span class="listing-room-name" data-res-id="' . $todayCheckOut->getId() . '"> ' . $todayCheckOut->getRoom()->getName() . ' #' . $todayCheckOut->getId() . ' </span>
                        </div>
                        </div>
                    </div>';
            }
        }


        return $htmlString;
    }

    function createCSV($reservations, $fileName): void
    {
        try {
            $cfile = fopen($fileName . '_reservations.csv', 'w');

//Inserting the table headers
            $header_data = array('id', 'room_name', 'check_in', 'check_out', 'guest_name', 'phone_number');
            fputcsv($cfile, $header_data);

//Data to be inserted
            $allReservations = array();

            foreach ($reservations as $reservation) {
                $row = array($reservation->GetId(), $reservation->getRoom()->getName(), $reservation->getCheckIn()->format('Y-m-d'), $reservation->getCheckOut()->format('Y-m-d'), $reservation->getGuest()->getName(), $reservation->getGuest()->getPhoneNumber());
                $allReservations[] = $row;
            }

// save each row of the data
            foreach ($allReservations as $row) {
                fputcsv($cfile, $row);
            }

// Closing the file
            fclose($cfile);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }


    }

    function createFlatFile($reservations, $fileName): void
    {
        try {
            $cfile = fopen($fileName . '_reservations.txt', 'w');

//Data to be inserted
            $allReservations = array();

            foreach ($reservations as $reservation) {
                $reservationId = str_pad($reservation->GetId(), 5, "0", STR_PAD_LEFT);
                $roomName = str_pad($reservation->getRoom()->getName(), 36);
                $roomPrice = str_pad($reservation->getRoom()->getPrice(), 9,"0", STR_PAD_LEFT);
                $checkIn = str_pad($reservation->getCheckIn()->format('Y-m-d'), 10);
                $checkOut = str_pad($reservation->getCheckOut()->format('Y-m-d'), 10);
                $guestName = str_pad($reservation->getGuest()->getName(), 36);
                $guestPhoneNumber = str_pad($reservation->getGuest()->getPhoneNumber(), 18);
                $origin = str_pad($reservation->getOrigin(), 46);
                $originURL = str_pad($reservation->getOriginUrl(), 46);
                $uid = str_pad($reservation->getUid(), 26);
                $additionalInformation = str_pad($reservation->getAdditionalInfo(), 108);
                $receivedOn = str_pad($reservation->getReceivedOn()->format('Y-m-d'), 10);
                $row = $reservationId. $roomName . $roomPrice . $checkIn . $checkOut. $guestName. $guestPhoneNumber . $origin . $originURL . $uid . $additionalInformation.  $receivedOn . "\n";

                fwrite($cfile, $row);
            }

            fclose($cfile);
        } catch (\Exception $exception) {
            fclose($cfile);
            $this->logger->debug($exception->getMessage());
        }
    }

}