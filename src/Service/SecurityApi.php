<?php

namespace App\Service;

use App\Entity\Guest;
use App\Entity\Property;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

require_once(__DIR__ . '/../app/application.php');

class SecurityApi
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function login($pin): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $property = $this->em->getRepository(Property::class)->findOneBy(array('secret' => $pin));
            if ($property != null) {
                if(session_id() === ''){
                    session_start();
                }

                $_SESSION["PROPERTY_ID"] = $property->getID();
                $responseArray[] = array(
                    'property_id' => $property->getID(),
                    'result_message' => "Success",
                    'result_code' => 0
                );
                return $responseArray;
            } else {
                $responseArray[] = array(
                    'result_message' => "Failed to authenticate the pin $pin",
                    'result_code' => 1
                );
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info(print_r($responseArray, true));
        }
        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function logout(): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try{
            // remove all session variables
            session_unset();
            // destroy the session
            session_destroy();
            $responseArray[] = array(
                'result_message' => 'Successfully logged out',
                'result_code' => 0
            );
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }

        $this->logger->info(print_r($responseArray, true));
        return $responseArray;
    }
}