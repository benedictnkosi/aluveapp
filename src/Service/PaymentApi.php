<?php

namespace App\Service;

use App\Entity\Payments;
use App\Entity\Reservations;
use App\Entity\ReservationStatus;
use App\Helpers\SMSHelper;
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
        if(session_id() === ''){
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
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->debug("failed to get payments " . print_r($responseArray, true));
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
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->error($ex->getMessage());
            return $ex->getMessage();
        }
    }

    public function addPayment($resId, $amount): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            $reservation = $this->em->getRepository(Reservations::class)->findOneBy(array('id' => $resId));
            $payment = new Payments();
            $now = new DateTime('today midnight');

            $payment->setReservation($reservation);
            $payment->setAmount($amount);
            $payment->setDate($now);

            $this->logger->debug("reservation status is pending" . $reservation->getStatus()->getName());

            //updated status to confirmed if it is pending
            if(strcmp($reservation->getStatus()->getName(), "pending") ===0) {
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

                    if (str_starts_with($reservation->getGuest()->getPhoneNumber(), '0') || str_starts_with($reservation->getGuest()->getPhoneNumber(), '+27')) {
                        $this->sendSMSToGuest($reservation);
                        $responseArray[] = array(
                            'result_code' => 0,
                            'result_message' => 'Successfully added payment'
                        );
                    }else{
                        if (!empty($reservation->getGuest()->getEmail())) {
                            $this->sendEmailToGuest($reservation->getGuest()->getEmail(), $amount);
                            $responseArray[] = array(
                                'result_code' => 0,
                                'result_message' => 'Successfully added payment'
                            );
                        }else{
                            $responseArray[] = array(
                                'result_code' => 0,
                                'result_message' => 'Successfully added payment, but email or sms not sent'
                            );
                        }
                    }

                    $this->logger->debug("no errors adding payment for reservation $resId. amount $amount");
                } else {

                    $responseArray[] = array(
                        'result_code' => 1,
                        'result_message' => 'This room is not available anymore. payment not added'
                    );
                }
            }else{
                //commit the payment changes
                $this->em->persist($payment);
                $this->em->flush($payment);
                $responseArray[] = array(
                    'result_code' => 0,
                    'result_message' => 'Successfully added payment'
                );
                $this->sendSMSToGuest($reservation);
            }

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->debug("failed to get payments " . print_r($responseArray, true));
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
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->debug("failed to get payments " . print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    function sendSMSToGuest( $reservation): void
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try{

            //send sms to guest
            $smsHelper = new SMSHelper($this->logger);
            $amountDue = $this->getTotalDue($reservation->getId());
            $messageBody = "Hi " . $reservation->getGuest()->getName() . ", Thank you for payment. Balance is R" . $amountDue . ". View your receipt http://".SERVER_NAME."/invoice.html?reservation=" . $reservation->getId();
            $smsHelper->sendMessage($reservation->getGuest()->getPhoneNumber(), $messageBody);
            $this->logger->debug("Successfully sent sms to guest");
        }catch (Exception $ex){
            $this->logger->debug(print_r($ex, true));
        }

    }

    function sendEmailToGuest( $reservation, $amountPaid): void
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try{
            //send email to guest
            $amountDue = $this->getTotalDue($reservation->getId());
            $emailBody = file_get_contents(__DIR__ . '/../email_template/thank_you_for_payment.html');
            $emailBody = str_replace("guest_name",$reservation->getGuest()->getName(),$emailBody);
            $emailBody = str_replace("amount_paid",$amountPaid,$emailBody);
            $emailBody = str_replace("amount_balance",$amountDue,$emailBody);
            $emailBody = str_replace("server_name",$reservation->getRoom()->getProperty()->getServerName(), $emailBody);
            $emailBody = str_replace("reservation_id",$reservation->getId(),$emailBody);
            $emailBody = str_replace("property_name",$reservation->getRoom()->getProperty()->getName(),$emailBody);

            $whitelist = array('localhost', '::1' );
            // check if the server is in the array
            if ( !in_array( $_SERVER['REMOTE_ADDR'], $whitelist ) ) {
                mail($reservation->getGuest()->getEmail(), 'Thank you for payment', $emailBody);
                $this->logger->debug("Successfully sent email to guest");
            }else{
                $this->logger->debug("local server email not sent");
            }


        }catch (Exception $ex){
            $this->logger->debug(print_r($ex, true));
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
}