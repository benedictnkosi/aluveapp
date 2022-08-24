<?php

namespace App\Controller;

use App\Service\FlipabilityApi;
use App\Service\WebScrapperApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScrapperController extends AbstractController
{

    /**
     * @Route("/scrap/")
     */
    public function home(LoggerInterface $logger, WebScrapperApi $webScrapperApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $responseArray = $webScrapperApi->scrapPage();
        return new JsonResponse( $responseArray, 200, array());
    }

}