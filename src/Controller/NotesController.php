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
     * @Route("api/note/add")
     */
    public function addNote(LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, NotesApi $notesApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('post')) {
            return new JsonResponse("Internal server error" , 500, array());
        }

        $response = $notesApi->addNote($request->get('id'), str_replace("+", "", $request->get('note')));
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        return $response;
    }
}