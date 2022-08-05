<?php

namespace App\Controller;


use App\Service\GuestApi;
use App\Service\PropertyApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HomeController extends AbstractController
{

    /**
     * @Route("/admin/", name="app_admin")
     */
    public function app_admin(LoggerInterface $logger): Response
    {
        if($this->getUser()->getProperty()->getId()){
            $logger->info("Session: " . print_r($_SESSION, true));
            $logger->info("user roles: " . print_r($this->getUser()->getRoles(), true));
            $logger->info("property name is: " . $this->getUser()->getProperty()->getId());
            $_SESSION["PROPERTY_ID"] = $this->getUser()->getProperty()->getId();
            $logger->info("new session: " . print_r($_SESSION, true));
            return $this->render('admin.html');
        }else{
            return $this->redirectToRoute("index");
        }
    }

    /**
     * @Route("/index.html", name="index")
     */
    public function index(): Response
    {
        return $this->render("index.html");
    }

    /**
     * @Route("/", name="home")
     */
    public function home(): Response
    {
        return $this->render("index.html");
    }

    #[Route('/signup', name: 'app_signup')]
    public function signup(): Response
    {

     return $this->render("signup.html");
    }

    #[Route('/booking', name: 'booking')]
    public function booking(): Response
    {

        return $this->render("booking.html");
    }


    #[Route('/confirmation', name: 'confirmation')]
    public function confirmation(): Response
    {
        return $this->render("confirmation.html");
    }

    #[Route('/cancelled', name: 'payment_cancelled')]
    public function payment_cancelled(): Response
    {
        return $this->render("cancelled.html");
    }

    #[Route('/thankyou', name: 'thank_you_for_payment')]
    public function thank_you_for_payment(): Response
    {
        return $this->render("thankyou.html");
    }

    #[Route('/invoice', name: 'invoice')]
    public function invoice(): Response
    {
        return $this->render("invoice.html");
    }

    /**
     * @Route("/public/userloggedin")
     */
    public function isUserLoggedIn(LoggerInterface $logger, Request $request, GuestApi $guestApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__ );
        if(isset($_SESSION["PROPERTY_ID"])){
            $response = array("logged_in" => "true");
        }else{
            $response = array("logged_in" => "false");
        }
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

}