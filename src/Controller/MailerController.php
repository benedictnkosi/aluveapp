<?php

namespace App\Controller;

use App\Service\EmailService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MailerController extends AbstractController
{

    /**
     * @Route("api/mail/send")
     */
    public function sendEmail(EmailService $emailService, LoggerInterface $logger): Response
    {
        $responseArray = array();

        try {
            $logger->info("calling sendEmail ");
            $emailService->sendEmail("testing from code", "admin@aluvegh.co.za", "Testing this Ish");

            $responseArray[] = array(
                'result_message' => "Success",
                'result_code'=> 0
            );
        } catch (\Exception) {
            $responseArray[] = array(
                'result_message' =>"Fail",
                'result_code'=> 1
            );
        }

        return new JsonResponse($responseArray , 200, array());
    }
}