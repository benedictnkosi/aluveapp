<?php

namespace App\Service;

use App\Entity\AddOns;
use App\Entity\Property;
use App\Entity\Rooms;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class PropertyApi
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        if (session_id() === '') {
            $logger->info("Session id is empty" . __METHOD__);
            session_start();
        }
    }


    public function getPropertyDetails($propertyId): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $property = $this->em->getRepository(Property::class)->findOneBy(
                array("id" => $propertyId));
            if ($property != null) {
                $responseArray[] = array(
                    'id' => $property->getId(),
                    'name' => $property->getName(),
                    'address' => $property->getAddress(),
                    'phone_number' => $property->getPhoneNumber(),
                    'email' => $property->getEmailAddress(),
                    'facebook' => $property->getFacebook(),
                    'twitter' => $property->getTwitter(),
                    'instagram' => $property->getInstagram(),
                    'result_code' => 0
                );

            } else {
                $responseArray[] = array(
                    'result_message' => 'property not found',
                    'result_code' => 1
                );
            }
            return $responseArray;

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

    public function getPropertyIdByUid($propertyUid)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        try {
            $property = $this->em->getRepository(Property::class)->findOneBy(
                array("uid" => $propertyUid));
            if ($property != null) {
                return $property->getId();
            } else {
                return null;
            }
        } catch (Exception) {
            return null;
        }
    }

    public function getPropertyUidByHost($request)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $propertyUid = null;
        try {
            $referer = $request->headers->get('referer');
            $host = parse_url($referer, PHP_URL_HOST);
            $this->logger->info("referrer is " . $referer);
            $this->logger->info("referrer host is " . $host);

            $property = $this->em->getRepository(Property::class)->findOneBy(
                array("serverName" => $host));
            if ($property != null) {
                $propertyUid = $property->getUid();
                $this->logger->info("property uid found for host $propertyUid - " . $host);
            } else {
                $this->logger->info("property uid NOT found for host " . $host);
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info(print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $propertyUid;
    }


    public function contactUs($guestName, $email, $phoneNumber, $message, $request): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $propertyApi = new PropertyApi($this->em, $this->logger);
            $propertyUid = $propertyApi->getPropertyUidByHost($request);
            if ($propertyUid === null) {
                $responseArray[] = array(
                    'result_message' => 'Error finding property details',
                    'result_code' => 1
                );
                return $responseArray;
            } else {
                $property = $this->em->getRepository(Property::class)->findOneBy(
                    array("uid" => $propertyUid));
            }


            if ($property != null) {
                $emailPrefix = "Message from $guestName\r\n Phone: $phoneNumber \r\nEmail: $email";
                $headers = 'From:' . $property->getEmailAddress() . "\r\n" .
                    'Reply-To: ' . $email . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
                mail($property->getEmailAddress(), "Website - Message from guest", $emailPrefix . $message, $headers);
                $responseArray[] = array(
                    'result_message' => 'Successfully sent message. Thank you',
                    'result_code' => 1
                );
            } else {
                $responseArray[] = array(
                    'result_message' => 'property not found',
                    'result_code' => 1
                );
            }
            return $responseArray;
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
}