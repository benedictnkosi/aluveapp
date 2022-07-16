<?php

namespace App\Helpers\FormatHtml;

use App\Service\AddOnsApi;
use App\Service\BlockedRoomApi;
use App\Service\CleaningApi;
use App\Service\GuestApi;
use App\Service\NotesApi;
use App\Service\PaymentApi;
use App\Service\ReservationApi;
use App\Service\RoomApi;
use DateInterval;
use DateTime;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

class CalendarHTML
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function formatHtml(): string
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $htmlString = "";

        $guestApi = new GuestApi($this->em, $this->logger);
        $roomsApi = new RoomApi($this->em, $this->logger);
        $reservationApi = new ReservationApi($this->em, $this->logger);
        $blockRoomApi = new BlockedRoomApi($this->em, $this->logger);
        $numberOfDays = 60;

        //headings
        $htmlString .= "<tr><th class='calendar-table-header'>Room Name</th>";

        for ($x = 0; $x <= $numberOfDays; $x++) {
            $todayDate = new DateTime();
            $todayDate->add(DateInterval::createFromDateString('yesterday'));
            $tempDate = $todayDate->add(new DateInterval('P' . $x . 'D'));

            if (strcmp($tempDate->format('D'), "Sat") == 0 || strcmp($tempDate->format('D'), "Sun") == 0) {
                $htmlString .= '<th class="weekend">' . $tempDate->format('D') . '<br>' . $tempDate->format('d') . '</th>';
            } else {
                $htmlString .= '<th>' . $tempDate->format('D') . '<br>' . $tempDate->format('d') . '</th>';
            }


        }
        $htmlString .= '</tr>';

        $rooms = $roomsApi->getRoomsEntities(0);
        foreach ($rooms as $room) {
            $htmlString .= '<tr><th class="headcol">' . $room->getName() . '</th>';
            $reservations = $reservationApi->getUpComingReservations($room->getId());
            $blockedRooms = $blockRoomApi->getBlockedRooms($room->getId());

            $this->logger->info("reservations found for room " . $room->getName() . " " . count($reservations));
            $this->logger->info("blocked found for room " . $room->getName() . " " . count($blockedRooms));

            if (count($reservations) < 1 && count($blockedRooms) < 1) {
                $htmlString .= str_repeat('<td class="available"></td>', $numberOfDays + 1);
            } else {
                for ($x = 0; $x <= $numberOfDays; $x++) {
                    $todayDate = new DateTime();
                    $todayDate->add(DateInterval::createFromDateString('yesterday'));
                    $tempDate = $todayDate->add(new DateInterval('P' . $x . 'D'));
                    $isDateBlocked = false;
                    $isDateBooked = false;
                    $isDateBookedButOpen = false;
                    $resID = "";
                    $guestName = "";
                    $blockNote = "";

                    $this->logger->info("outside foreach for reservations temp date is " . $todayDate->format("Y-m-d") . " x is $x");

                    foreach ($reservations as &$reservation) {
                        $isCheckInDay = false;
                        $this->logger->info("Check if temp date " . $tempDate->format("Y-m-d") . " and res " . $reservation->getId() . " check in date is " . $reservation->getCheckIn()->format("Y-m-d") . "check out date " . $reservation->getCheckOut()->format("Y-m-d"));
                        if ($tempDate >= $reservation->getCheckIn() && $tempDate < $reservation->getCheckOut()) {
                            $this->logger->info("check passed");
                            if (strcasecmp($reservation->getStatus()->getName(), "confirmed") === 0) {
                                $resID = $reservation->getId();
                                $isDateBooked = true;
                                $guestName = $reservation->getGuest()->getName();
                                if (strcasecmp($tempDate->format("Y-m-d"), $reservation->getCheckIn()->format("Y-m-d")) === 0) {
                                    $this->logger->info("Check in day is true because tempdate is " . $tempDate->format("Y-m-d") . " and res " . $reservation->getId() . " check in date is " . $reservation->getCheckIn()->format("Y-m-d"));
                                    $isCheckInDay = true;
                                }
                                break;
                            } else if (strcasecmp($reservation->getStatus()->getName(), "pending") == 0) {
                                $isDateBookedButOpen = true;
                                break;
                            }

                        } else {
                            $this->logger->info("check failed");

                        }
                    }

                    $this->logger->info("blocked rooms");

                    foreach ($blockedRooms as &$blockedRoom) {
                        if ($tempDate >= $blockedRoom->getFromDate() && $tempDate < $blockedRoom->getToDate()) {
                            $isDateBlocked = true;
                            $blockNote = $blockedRoom->getComment();
                            break;
                        }
                    }

                    $this->logger->info("checking if date booked");
                    if ($isDateBooked) {
                        if ($isCheckInDay === true) {
                            $htmlString .= '<td  class="booked checkin" resid="' . $resID . '" title="' . $resID . '"><img  src="images/' . $reservation->getOrigin() . '.png"  resid="' . $resID . '" alt="checkin" class="image_checkin"></td>';
                        } else {
                            $htmlString .= '<td  class="booked" resid="' . $resID . '" title="' . $guestName . '"></td>';
                        }


                    } else if ($isDateBlocked) {
                        $htmlString .= '<td class="blocked" title="' . $blockNote . '"></td>';
                    } else if ($isDateBookedButOpen) {
                        $htmlString .= '<td class="pending"></td>';
                    } else {
                        $htmlString .= '<td class="available"></td>';
                    }

                }
            }
            $htmlString .= '</tr>';
        }
        return $htmlString;
    }
}