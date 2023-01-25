<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailerController extends AbstractController
{

    /**
     * @Route("api/mail/send")
     */
    public function sendEmail(MailerInterface $mailer): Response
    {
        $responseArray = array();

        $email = (new Email())
            ->from('info@aluvegh.co.za')
            ->to('admin@aluvegh.co.za')
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!');

        try {

            $mailer->send($email);
            $responseArray[] = array(
                'result_message' => "Success",
                'result_code'=> 0
            );
        } catch (\Exception|TransportExceptionInterface $e) {
            $responseArray[] = array(
                'result_message' =>"Fail",
                'result_code'=> 1
            );
        }

        return new JsonResponse($responseArray , 200, array());
    }
}