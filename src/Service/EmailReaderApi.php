<?php

namespace App\Service;

use App\Entity\Property;
use App\Entity\Reservations;
use App\Helpers\SMSHelper;
use Exception;
use phpDocumentor\Reflection\Types\Void_;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Guest;

class EmailReaderApi
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    function getAirbnbGuestName($confirmationCode)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $mailConn = imap_open("{".EMAIL_SERVER.":".EMAIL_SERVER_PORT."/pop3/ssl/novalidate-cert}", EMAIL_ADDRESS, EMAIL_PASSWORD);

        $search = 'ON ' . date('d-M-Y'); // search for today's email only
        $emails = imap_search($mailConn, $search);
        $guestName = null;
        if ($emails) {
            foreach ($emails as $emailID) {
                $overview = imap_fetch_overview($mailConn, $emailID, 0);
                $emailSubject = $overview[0]->subject;
                $this->logger->info($emailSubject);

                try {
                    $pos = strpos($emailSubject, 'Reservation confirmed');
                    if ($pos !== false) {

                        $emailMsgNumber = $overview[0]->msgno;

                        $bodyText = imap_fetchbody($mailConn, $emailMsgNumber, 1);
                        if (! strlen($bodyText) > 0) {
                            $this->logger->info("body is empty");
                            $bodyText = imap_fetchbody($mailConn, $emailMsgNumber, 1);
                        }

                        $bodyText = quoted_printable_decode($bodyText);


                        $this->logger->info("Code " . $confirmationCode);
                        if(str_contains($bodyText, $confirmationCode)){
                            $guestName = $this->getAirbnbGuestNameFromSubject($emailSubject);
                            $this->logger->info("Guest name " .$guestName);
                            return $guestName;
                        }
                    }
                } catch (\Throwable $e) {
                    $temparray1 = array(
                        'result_code' => 1,
                        'result_desciption' => "Exception occured"
                    );

                    echo json_encode($temparray1);
                    print_r($e);
                }
            }

            return $guestName;
        } else {
            $temparray1 = array(
                'result_code' => 0,
                'result_desciption' => "no emails found"
            );

            echo json_encode($temparray1);
        }
    }

    function getAirbnbGuestNameFromSubject($subject){
        $len = strlen("Reservation confirmed - ");
        $pos = strpos($subject, "arrives");
        $nameLength = $pos - $len;
        return substr($subject, $len, $nameLength);

    }

}