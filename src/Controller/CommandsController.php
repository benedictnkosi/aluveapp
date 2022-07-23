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
use Symfony\Component\Filesystem\Filesystem;

class CommandsController extends AbstractController
{


    /**
     * @Route("api/runcommand/clear")
     */
    public function runCommand(LoggerInterface $logger): Response
    {
        $logger->info("Starting Method: " . __METHOD__);

        if(function_exists('exec')) {
            echo "exec is enabled";
        }else{
            echo "exec is not enabled";
        }

        $command = 'php ../bin/console doctrine:cache:clear-metadata';
        exec($command, $result);
        $responseArray[] = array(
            'command' =>  $command,
            'result_message' => print_r($result, true),
            'result_code' => 0
        );

        $command = 'php ../bin/console doctrine:cache:clear-query';
        exec($command, $result);
        $responseArray[] = array(
            'command' =>  $command,
            'result_message' => print_r($result, true),
            'result_code' => 0
        );

        $command = 'php ../bin/console doctrine:cache:clear-result';
        exec($command, $result);
        $responseArray[] = array(
            'command' =>  $command,
            'result_message' => print_r($result, true),
            'result_code' => 0
        );
        return new JsonResponse( $responseArray, 200, array());
    }

    /**
     * @Route("api/runcommand/phpmemory")
     */
    public function checkPHPMemory(LoggerInterface $logger): Response
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
    public function gitVersion(LoggerInterface $logger): Response
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

    /**
     * @Route("api/runcommand/gitpull")
     */
    public function gitPull(LoggerInterface $logger): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $command = 'git pull origin main --force';
        exec($command, $result);
        $responseArray[] = array(
            'command' =>  $command,
            'result_message_auto' => print_r($result, true),
            'result_code' => 0
        );

        return new JsonResponse( $responseArray, 200, array());
    }

    /**
     * @Route("api/phpinfo")
     */
    public function clearsymfony(LoggerInterface $logger): Response
    {
        $fs = new Filesystem();
        $fs->remove($this->container->getParameter('kernel.cache_dir'));
        $responseArray[] = array(
            'result_code' => 0
        );

        return new JsonResponse( $responseArray, 200, array());
    }
}