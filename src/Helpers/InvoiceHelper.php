<?php

namespace App\Helpers;

use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

require_once(__DIR__ . '/../app/application.php');


class InvoiceHelper
{

    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function createInvoicePDF($guestName, $customerPhone, $resID, $checkin, $checkout, $price, $total, $resaNights, $rooName, $amountPaid)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);


        if (strcmp($amountPaid, "0") !== 0) {
            $header = "RECEIPT";
            $makePayment = "Your Booking is Confirmed!";
        }else{
            $header = "INVOICE";
            $makePayment = "\r\n\r\nPlease make a payment for the deposit R" . intval($total)/2 . ".00 to secure your room. Your room is still bookable on our websites.\r\n
50% Deposit is required to secure the booking.\r\n
We take Card payment, Cash and EFT. We, unfortunately, can not check you in with an outstanding balance.\r\n
Please email proof of payment to info@aluvegh.co.za";
        }

        $parameters = [
            'from' => 'Aluve Guesthouse - VAT 4010297762',
            'to' => $guestName . " " . $customerPhone,
            'logo' => "http://aluvegh.co.za/wp-content/uploads/2021/07/aluve-icon.png",
            'number' => $resID,
            'items[0][name]' => $rooName,
            'items[0][quantity]' => $resaNights,
            'items[0][description]' => "Arrival dates: " . $checkin . " \r\n  Departure date: " . $checkout,
            'items[0][unit_cost]' => $price,
            //'tax_title' => "VAT",
            //'fields[tax]' => "%",
            //'tax' => 15,
            'notes' => $makePayment . "
\r\n
Banking Details:\r\n
Bank: FNB\r\n
Name: Aluve Guesthouse\r\n
Acc: 62788863241\r\n
branch: 250 655\r\n
\r\n
\r\n
Guest House Address: \r\n
187 kitchener Avenue\r\n
kensington\r\n
Johannesburg 2094\r\n
\r\n
Contact details:\r\n
Cell: +27 79 634 7610\r\n
Alt Cell: +27 83  791 7430\r\n
Email: info@aluvegh.co.za\r\n
\r\n
\r\n
See you soon!\r\n
\r\n
",
            'terms' => "No noise after 6pm\r\n
No loud music\r\n
No parties\r\n
No smoking inside the house\r\n
No kids under the age of 12\r\n
Check-in cut-off is at 22:00. Please make arrangements for a later check-in\r\n
The gate auto closes in 2 minutes, We have sensors installed and we will not be responsible for any damage caused by the gate\r\n
\r\n
Cancellation:
\r\n
The guest can cancel free of charge until 7 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 7 days before arrival. If the guest doesnt show up they will be charged the total price of the reservation.\r\n
\r\n
Load Shedding:
\r\n
Please use the link below to see the load shedding schedule for our area, B4.\r\n
https://www.citypower.co.za/customers/Pages/Load_Shedding_Downloads.aspx\r\n
\r\n       
We look forward to hosting you\r\n
        
Aluve Guesthouse\r\n
",

            "currency" => "ZAR",
            "amount_paid" => $amountPaid,
            "header" => $header
        ];

        try {
            $ch = curl_init();
            $fp = fopen(__DIR__ . '/../../../invoices/' . $resID . ".pdf", "w");

            // set url
            curl_setopt($ch, CURLOPT_URL, "https://invoice-generator.com");

            curl_setopt($ch, CURLOPT_POST, 1);


            //print_r($parameters);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));

            curl_setopt($ch, CURLOPT_FILE, $fp);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);


            // $output contains the output string

            $output = curl_exec($ch);

            fwrite($fp, $output);

            fclose($fp);

        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());
            return false;
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);

        return true;
    }

}

