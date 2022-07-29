<?php

namespace App\Controller;


use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    /**
     * @Route("/admin/", name="app_admin")
     */
    public function app_admin(): Response
    {
        if($this->getUser()->getProperty()->getId()){
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

    /**
     * @Route("/signup", name="signup")
     */
    public function signup(): Response
    {
        return $this->render("signup.html");
    }

}