<?php

namespace App\Controller;

use App\Service\NotesApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class NotesController extends AbstractController
{
    /**
     * @Route("api/note/{reservationId}/text/{text}")
     */
    public function addNote($reservationId, $text, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, NotesApi $notesApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $notesApi->addNote($reservationId, str_replace("+", "", $text));
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }
}