<?php

namespace App\Controller;

use App\Service\RoomApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ImageController extends AbstractController
{

    /**
     * @Route("api/configuration/removeimage/{imageId}")
     */
    public function removeImage($imageId, LoggerInterface $logger,Request $request, EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->removeImage($imageId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/configuration/markdefault/{imageId}")
     */
    public function markDefault($imageId, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->markDefault($imageId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

}