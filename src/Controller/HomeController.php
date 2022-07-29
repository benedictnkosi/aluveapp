<?php

namespace App\Controller;


use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function home(LoggerInterface $logger): Response
    {
        return $this->render("admin.html");
    }

    /**
     * @Route("/landing", name="landing")
     */
    public function landing(): Response
    {
        return $this->render("index.html");
    }
}