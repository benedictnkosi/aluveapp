<?php

namespace App\Controller;

use App\Service\PropertyApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class CommandsController extends AbstractController
{


    /**
     * @Route("api/runcommand/clear")
     */
    public function runCommand(LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PropertyApi $propertyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $command = 'php ../bin/console doctrine:cache:clear-metadata';
        exec($command, $result);
        $responseArray[] = array(
            'command' =>  $command,
            'result_message' => print_r($result[3], true),
            'result_code' => 0
        );

        $command = 'php ../bin/console doctrine:cache:clear-query';
        exec($command, $result);
        $responseArray[] = array(
            'command' =>  $command,
            'result_message' => print_r($result[3], true),
            'result_code' => 0
        );

        $command = 'php ../bin/console doctrine:cache:clear-result';
        exec($command, $result);
        $responseArray[] = array(
            'command' =>  $command,
            'result_message' => print_r($result[3], true),
            'result_code' => 0
        );
        return new JsonResponse( $responseArray, 200, array());
    }

    /**
     * @Route("api/runcommand/phpmemory")
     */
    public function checkPHPMemory(LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PropertyApi $propertyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $command = 'php -i | grep "memory_limit"';
        exec($command, $result);
        $responseArray[] = array(
            'command' =>  $command,
            'result_message' => print_r($result, true),
            'result_code' => 0
        );

        return new JsonResponse( $responseArray, 200, array());
    }

    /**
     * @Route("api/runcommand/gitversion")
     */
    public function gitVersion(LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, PropertyApi $propertyApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $command = 'git --version';
        exec($command, $result);
        $responseArray[] = array(
            'command' =>  $command,
            'result_message' => print_r($result, true),
            'result_code' => 0
        );

        return new JsonResponse( $responseArray, 200, array());
    }
}