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


class ReservationHtml
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
									</div>';

        if(strcmp($period, 'past')===0){
            $numberOfDays = 180;
            for ($x = 0; $x <= $numberOfDays; $x++) {
                $todayDate = new DateTime();
                $todayDate->add(DateInterval::createFromDateString('yesterday'));
                $tempDate = $todayDate->sub(new DateInterval('P' . $x . 'D'));
                $htmlString .= $this->helper($tempDate, $reservations);
            }
        }else{
            $numberOfDays = 180;
            for ($x = 1; $x <= $numberOfDays; $x++) {
                $todayDate = new DateTime();
                $todayDate->add(DateInterval::createFromDateString('yesterday'));
                $tempDate = $todayDate->add(new DateInterval('P' . $x . 'D'));
                if(strcmp($period, 'pending')===0){
                    $htmlString .= $this->helper($tempDate, $reservations, false);
                }else{
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
        $htmlString = "";
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
            $htmlString .= '<div class="reservation-item" data-res-id="'.$todayCheckIn->getId().'">
                         <div class="listing-description clickable open-reservation-details" data-res-id="'.$todayCheckIn->getId().'">
                          <img class="listing-checkin-image listing-image" src="/admin/images/listing-checkin.png" data-res-id="'.$todayCheckIn->getId().'"></img>
                        <img class="listing-image-origin" src="/admin/images/'.$todayCheckIn->getOrigin().'.png" data-res-id="'.$todayCheckIn->getId().'"></img>
                        <div class="listing-description-text" data-res-id="'.$todayCheckIn->getId().'">'
                . $todayCheckIn->getGuest()->getName() . ' is expected to check-in 
                         <span class="listing-room-name" data-res-id="'.$todayCheckIn->getId().'"> ' . $todayCheckIn->getRoom()->getName() . ' #'.$todayCheckIn->getId().'</span>
                        </div>
                        </div>
                    </div>';
        }

        if($outputCheckOuts){
            foreach ($todayCheckOuts as $todayCheckOut) {
                $htmlString .= '<div class="reservation-item" data-res-id="'.$todayCheckOut->getId().'">
                         <div class="listing-description clickable open-reservation-details" data-res-id="'.$todayCheckOut->getId().'">
                          <img class="listing-checkin-image listing-image" src="/admin/images/listing-checkout.png" data-res-id="'.$todayCheckOut->getId().'"></img>
                        <img class="listing-image-origin" src="/admin/images/'.$todayCheckOut->getOrigin().'.png" data-res-id="'.$todayCheckOut->getId().'"></img>
                        <div class="listing-description-text" data-res-id="'.$todayCheckOut->getId().'">'
                    . $todayCheckOut->getGuest()->getName() . ' is expected to check-out 
                         <span class="listing-room-name" data-res-id="'.$todayCheckOut->getId().'"> ' . $todayCheckOut->getRoom()->getName() . ' #'.$todayCheckOut->getId().' </span>
                        </div>
                        </div>
                    </div>';
            }
        }


        return $htmlString;
    }
}