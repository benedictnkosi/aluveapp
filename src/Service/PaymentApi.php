<?php

namespace App\Service;

use App\Entity\Payments;
use App\Entity\Reservations;
use App\Entity\ReservationStatus;
use App\Helpers\DatabaseHelper;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

require_once(__DIR__ . '/../app/application.php');

class PaymentApi
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        if (session_id() === '') {
            $logger->info("Session id is empty");
            session_start();
        }
    }

    public function getReservationPayments($resId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $payments = $this->em->getRepository(Payments::class)->findBy(array('reservation' => $resId));
            $this->logger->debug("no errors finding payments for reservation $resId. payment count " . count($payments));
            return $payments;
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("failed to get payments " . print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function getReservationPaymentsHtml($resId): string
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            $payments = $this->em->getRepository(Payments::class)->findBy(array('reservation' => $resId));
            $html = "";
            foreach ($payments as $payment) {
                $html .= '<tr class="item">
					<td></td>
					<td>Payment</td>
					<td> ' . $payment->getDate()->format("d-M") . '</td>
					<td>-R' . number_format((float)$payment->getAmount(), 2, '.', '') . '</td>
				</tr>';
            }
            return $html;
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error($ex->getMessage());
            return $ex->getMessage();
        }
    }

    public function addPayment($resId, $amount, $reference, $channel = null): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {


            $resId = str_replace("[", "", $resId);
            $resId = str_replace("]", "", $resId);
            $reservationIdsArray = explode(",", $resId);
            $numberOfReservations = count($reservationIdsArray);

            foreach ($reservationIdsArray as $resId) {
                $reservation = $this->em->getRepository(Reservations::class)->findOneBy(array('id' => $resId));
                $payment = new Payments();
                $now = new DateTime();


                            $this->logger->debug("Status is: " . $reservation->getStatus()->getName());

                if(intval($amount) < 200 && strcmp($reservation->getStatus()->getName(), "pending") == 0){
                    $responseArray[] = array(
                        'result_code' => 1,
                        'result_message' => 'Payment of less than R200 not allowed for pending reservations'
                    );
                    return $responseArray;
                }

                $payment->setReservation($reservation);
                $amountPerReservation = intval($amount) / intval($numberOfReservations);
                $payment->setAmount($amountPerReservation);
                $payment->setDate($now);
                $payment->setChannel($channel);
                $payment->SetReference($reference);

                $this->logger->debug("reservation status is " . $reservation->getStatus()->getName());

                //updated status to confirmed if it is pending
                if (strcmp($reservation->getStatus()->getName(), "pending") === 0) {
                    $roomApi = new RoomApi($this->em, $this->logger);

                    $isRoomAvailable = $roomApi->isRoomAvailable($reservation->getRoom()->getId(), $reservation->getCheckIn()->format("Y-m-d"), $reservation->getCheckOut()->format("Y-m-d"));
                    if ($isRoomAvailable) {
                        $this->logger->debug("room is available");
                        $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => "confirmed"));
                        $reservation->setStatus($status);
                        //commit the reservation changes
                        $this->em->persist($reservation);
                        $this->em->flush($reservation);

                        //commit the payment changes
                        $this->em->persist($payment);
                        $this->em->flush($payment);

                        //block connected Room
                        $blockRoomApi = new BlockedRoomApi($this->em, $this->logger);
                        $blockRoomApi->blockRoom($reservation->getRoom()->getLinkedRoom(), $reservation->getCheckIn()->format("Y-m-d"), $reservation->getCheckOut()->format("Y-m-d"), "Connected Room Booked ", $reservation->getId());

                        //check google ads notification
                        $now = new DateTime();
                        if (strcmp($reservation->getCheckIn()->format("Y-m-d"), $now->format("Y-m-d")) === 0) {
                            $notificationApi = new NotificationApi($this->em, $this->logger);
                            $notificationApi->updateAdsNotification($reservation->getRoom()->getProperty()->getId());
                        }


                        $this->sendEmailToGuest($reservation, $amountPerReservation);
                        $responseArray[] = array(
                            'result_code' => 0,
                            'result_message' => 'Successfully added payment'
                        );

                        $this->logger->debug("no errors adding payment for reservation $resId. amount $amount");
                    } else {
                        if (strcmp($channel, "payfast") === 0) {
                            $communicationApi = new CommunicationApi($this->em, $this->logger);

                            //send email to guest house
                            $emailBody = file_get_contents(__DIR__ . '/../email_template/failed_payment_to_host.html');
                            $emailBody = str_replace("reservation_id", $reservation->getId(), $emailBody);
                            $emailBody = str_replace("amount_paid", $amount, $emailBody);
                            $emailBody = str_replace("property_name", $reservation->getRoom()->getProperty()->getName(), $emailBody);

                            $communicationApi->sendEmailViaGmail(ALUVEAPP_ADMIN_EMAIL, $reservation->getRoom()->getProperty()->getEmailAddress(), $emailBody, 'Aluve App - Adding payment failed');

                            //send email to guest
                            $emailBody = file_get_contents(__DIR__ . '/../email_template/failed_payment_to_guest.html');
                            $emailBody = str_replace("reservation_id", $reservation->getId(), $emailBody);
                            $emailBody = str_replace("amount_paid", $amount, $emailBody);
                            $emailBody = str_replace("property_name", $reservation->getRoom()->getProperty()->getName(), $emailBody);
                            $emailBody = str_replace("property_email", $reservation->getRoom()->getProperty()->getEmailAddress(), $emailBody);
                            $emailBody = str_replace("property_number", $reservation->getRoom()->getProperty()->getPhoneNumber(), $emailBody);
                            $emailBody = str_replace("guest_name", $reservation->getGuest()->getName(), $emailBody);

                            $communicationApi->sendEmailViaGmail(ALUVEAPP_ADMIN_EMAIL, $reservation->getGuest()->getEmail(), $emailBody, 'Aluve App - Adding payment failed', $reservation->getRoom()->getProperty()->getName(), $reservation->getRoom()->getProperty()->getEmailAddress());
                        }

                        $responseArray[] = array(
                            'result_code' => 1,
                            'result_message' => 'This room is not available anymore. payment not added'
                        );

                    }
                } else {
                    //commit the payment changes
                    $this->em->persist($payment);
                    $this->em->flush($payment);
                    $responseArray[] = array(
                        'result_code' => 0,
                        'result_message' => 'Successfully added payment'
                    );
                    $this->sendEmailToGuest($reservation, $amountPerReservation);
                }
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("failed to get payments " . print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function addDiscount($resId, $amount, $channel = null): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $reservation = $this->em->getRepository(Reservations::class)->findOneBy(array('id' => $resId));
            $payment = new Payments();

            $payment->setReservation($reservation);
            $payment->setAmount($amount);
            $payment->setDate(new DateTime());
            $payment->setChannel($channel);
            $payment->setDiscount(true);
            $payment->setReference("none");

            //commit the payment changes
            $this->em->persist($payment);
            $this->em->flush($payment);

            $responseArray[] = array(
                'result_code' => 0,
                'result_message' => 'Successfully added discount'
            );
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("failed to add discount " . print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function getTotalDue($resId): float|int|array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $reservation = $this->em->getRepository(Reservations::class)->findOneBy(array('id' => $resId));
            $roomPrice = 0;
            if (strcasecmp($reservation->getOrigin(), "website") == 0) {
                $roomPrice = $reservation->getRoom()->getPrice();
            }

            $totalDays = intval($reservation->getCheckIn()->diff($reservation->getCheckOut())->format('%a'));

            $addOnsApi = new AddOnsApi($this->em, $this->logger);
            $addOns = $addOnsApi->getReservationAddOns($resId);

            $this->logger->debug("looping add ons for reservation " . $resId . " add on count " . count($addOns));
            $totalPriceForAllAdOns = 0;
            foreach ($addOns as $addOn) {
                $totalPriceForAllAdOns += (intVal($addOn->getAddOn()->getPrice()) * intval($addOn->getQuantity()));
            }
            $totalPrice = intval($roomPrice) * $totalDays;
            $totalPrice += $totalPriceForAllAdOns;

            //payments
            $this->logger->debug("calculating payments " . $resId);
            $payments = $this->getReservationPayments($resId);
            $totalPayment = 0;
            foreach ($payments as $payment) {
                $totalPayment += (intVal($payment->getAmount()));
            }

            $due = $totalPrice - $totalPayment;

            $this->logger->debug("Due amount is $due");
            return $due;
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("failed to get payments " . print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    function sendEmailToGuest($reservation, $amountPaid): void
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            //send email to guest
            $amountDue = $this->getTotalDue($reservation->getId());
            $emailBody = file_get_contents(__DIR__ . '/../email_template/thank_you_for_payment.html');
            $emailBody = str_replace("guest_name", $reservation->getGuest()->getName(), $emailBody);
            $emailBody = str_replace("amount_paid", $amountPaid, $emailBody);
            $emailBody = str_replace("amount_balance", $amountDue, $emailBody);
            $emailBody = str_replace("server_name", $reservation->getRoom()->getProperty()->getServerName(), $emailBody);
            $emailBody = str_replace("reservation_id", $reservation->getId(), $emailBody);
            $emailBody = str_replace("property_name", $reservation->getRoom()->getProperty()->getName(), $emailBody);
            $emailBody = str_replace("room_name", $reservation->getRoom()->getName(), $emailBody);

            $communicationApi = new CommunicationApi($this->em, $this->logger);
            $communicationApi->sendEmailViaGmail(ALUVEAPP_ADMIN_EMAIL, $reservation->getGuest()->getEmail(), $emailBody, $reservation->getRoom()->getProperty()->getName() . '- Thank you for payment', $reservation->getRoom()->getProperty()->getName(), $reservation->getRoom()->getProperty()->getEmailAddress());
            $this->logger->debug("Successfully sent email to guest");
        } catch (Exception $ex) {
            $this->logger->error(print_r($ex, true));
        }
    }

    public function getReservationPaymentsTotal($resId): int
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            $payments = $this->em->getRepository(Payments::class)->findBy(array('reservation' => $resId));
            $totalPayment = 0;
            foreach ($payments as $payment) {
                $totalPayment += (intVal($payment->getAmount()));
            }
            return $totalPayment;
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            return 0;
        }
    }

    public function getReservationDiscountTotal($resId): int
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            $payments = $this->em->getRepository(Payments::class)->findBy(array('reservation' => $resId, 'channel' => 'discount'));
            $totalPayment = 0;
            foreach ($payments as $payment) {
                $totalPayment += (intVal($payment->getAmount()));
            }
            return $totalPayment;
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            return 0;
        }
    }

    public function getCashReport($startDate, $endDate, $channel)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();

        try {

            $sql = "SELECT SUM(amount) as totalCash FROM `payments`
            WHERE channel = '".$channel."'
            and   DATE(`date`) >= '" . $startDate . "'
            and  DATE(`date`) <= '" . $endDate . "'";

            $this->logger->info($sql);

            //echo $sql;
            $databaseHelper = new DatabaseHelper($this->logger);
            $result = $databaseHelper->queryDatabase($sql);


            if (!$result) {
                $responseArray[] = array(
                    'result_message' => 0,
                    'result_code' => 0
                );
            } else {
                $amount = 0;
                while ($results = $result->fetch_assoc()) {
                    if($results["totalCash"] !== null){
                        $amount = $results["totalCash"];
                    }

                    $this->logger->info("amount is " . $results["totalCash"]);
                }
                $responseArray[] = array(
                    'result_message' => number_format($amount,2),
                    'result_code' => 0
                );
            }
            return $responseArray;
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("failed to get occupancy " . print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function getCashReportByDay($startDate, $endDate,$channel): string
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $htmlResponse = "<tr><th>Date</th><th>Amount</th></tr>";

        try {

            $sql = "SELECT SUM(amount) as totalCash, LEFT( date, 10 ) as day FROM `payments`
            WHERE channel = '".$channel."'
            and   DATE(`date`) >= '" . $startDate . "'
            and  DATE(`date`) <= '" . $endDate . "'
GROUP BY LEFT( date, 10 ) 
order by date desc";

            $this->logger->info($sql);

            //echo $sql;
            $databaseHelper = new DatabaseHelper($this->logger);
            $result = $databaseHelper->queryDatabase($sql);

            if ($result) {
                while ($results = $result->fetch_assoc()) {
                    $htmlResponse .= "<tr><td>".$results["day"] ."</td><td>".$results["totalCash"]."</td></tr>";
                }
            }
            return $htmlResponse;
        } catch (Exception $ex) {

        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $htmlResponse;
    }

    public function getCashReportAllTransactions($startDate, $endDate,$channel): string
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $htmlResponse = "<tr><th>Date</th><th>Amount</th><th>Reference</th><th>Reservation</th></tr>";

        try {

            $sql = "SELECT amount, date, reservation_id, reference FROM `payments`
            WHERE channel = '".$channel."'
            and   DATE(`date`) >= '" . $startDate . "'
            and  DATE(`date`) <= '" . $endDate . "'
order by date desc";

            $this->logger->info($sql);

            //echo $sql;
            $databaseHelper = new DatabaseHelper($this->logger);
            $result = $databaseHelper->queryDatabase($sql);

            if ($result) {
                while ($results = $result->fetch_assoc()) {
                    $htmlResponse .= "<tr><td>".$results["date"] ."</td><td>".$results["amount"]."</td><td>".$results["reference"]."</td><td>".$results["reservation_id"]."</td></tr>";
                }
            }
            return $htmlResponse;
        } catch (Exception $ex) {

        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $htmlResponse;
    }


    public function removePayment($paymentId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $payment = $this->em->getRepository(Payments::class)->findOneBy(array('id' => $paymentId));
            $this->em->remove($payment);
            $this->em->flush($payment);

            $responseArray[] = array(
                'result_message' => "Successfully removed payment",
                'result_code' => 0
            );

        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            $responseArray[] = array(
                'result_message' =>$ex->getMessage(),
                'result_code' => 1
            );
        }

        return $responseArray;
    }

}