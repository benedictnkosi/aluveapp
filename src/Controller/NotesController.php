<?php

namespace App\Controller;

use App\Service\NotesApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class NotesController extends AbstractController
{
    /**
     * @Route("api/note/{reservationId}/text/{text}")
     */
    public function addNote($reservationId, $text, LoggerInterface $logger, EntityManagerInterface $entityManager, NotesApi $notesApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $notesApi->addNote($reservationId, str_replace("+", "", $text));
        return  $this->json($response);
    }
}