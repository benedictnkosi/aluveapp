<?php

namespace App\Service;

use App\Entity\Property;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

require_once(__DIR__ . '/../app/application.php');

class SecurityApi
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

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
                if (session_id() === '') {
                    session_start();
                }

                $responseArray[] = array(
                    'property_id' => $property->getId(),
                    'property_uid' => $property->getUid(),
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

    public function isLoggedInBoolean($propertyUid): bool
    {
        $result = $this->isLoggedIn($propertyUid);
        return $result[0]['logged_in'];
    }

    public function isLoggedIn($propertyUid): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $property = $this->em->getRepository(Property::class)->findOneBy(array('uid' => $propertyUid));
            if ($property != null) {
                $responseArray[] = array(
                    'logged_in' => true
                );
            } else {
                $responseArray[] = array(
                    'logged_in' => false
                );
            }

        } catch (Exception $ex) {
            $responseArray[] = array(
                'logged_in' => false,
                'exception' => $ex->getMessage()
            );
            $this->logger->info(print_r($responseArray, true));
        }
        return $responseArray;
    }


    public function logout(): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
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