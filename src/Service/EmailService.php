<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    function sendEmail($messageBody, $toEmail, $subject): bool
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        try {

            $body = wordwrap($messageBody, 70);
            $this->logger->info("1");
            // echo $body;
            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: ' . EMAIL_ADDRESS . "\r\n";
            $headers .= 'Reply-To: ' . EMAIL_ADDRESS . "\r\n";

            $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";

            if (strcasecmp($_SERVER['SERVER_NAME'], "1localhost") == 0) {
                $this->logger->info("1localhost");
                return true;
            } else {
                if (mail($toEmail, $subject, $body, $headers)) {
                    $this->logger->info("Email Sent");
                    return true;
                } else {
                    $this->logger->info("Email Not Sent");
                    return false;
                }
            }
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
            return false;
        }
    }


}