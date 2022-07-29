<?php

namespace App\Service;

use App\Entity\Property;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
require_once(__DIR__ . '/../app/application.php');

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
        $this->logger->debug("Starting Method: " . __METHOD__);
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
                    'bank_name' => $property->getBankName(),
                    'bank_account_type' => $property->getBankAccountType(),
                    'bank_account_number' => $property->getBankAccountNumber(),
                    'bank_branch_number' => $property->getBankBranchCode(),
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
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function getPropertyUidByHost($request)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $propertyUid = null;
        try {
            $referer = $request->headers->get('referer');
            $host = parse_url($referer, PHP_URL_HOST);
            $this->logger->debug("referrer is " . $referer);
            $this->logger->debug("referrer host is " . $host);

            $property = $this->em->getRepository(Property::class)->findOneBy(
                array("serverName" => $host));
            if ($property != null) {
                $propertyUid = $property->getUid();
                $this->logger->debug("property uid found for host $propertyUid - " . $host);
            } else {
                $this->logger->debug("property uid NOT found for host " . $host);
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $propertyUid;
    }

    public function getPropertyTerms($roomApi,  $request): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            if(!isset($_SESSION['PROPERTY_ID'])){
                $propertyUid = $this->getPropertyUidByHost($request);
            }else{
                $propertyUid = $_SESSION['PROPERTY_ID'];
            }

            $property = $this->em->getRepository(Property::class)->findOneBy(
                array("id" =>$propertyUid));

            $responseArray[] = array(
                'terms' => $property->getTerms(),
                'terms_html' => $roomApi->replaceWithBold($property->getTerms()),
                'result_code' => 0,
            );
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function updatePropertyTerms( $terms)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            $property = $this->em->getRepository(Property::class)->findOneBy(
                array("id" => $_SESSION['PROPERTY_ID']));
            if ($property != null) {
                $property->setTerms($terms);
                $this->em->persist($property);
                $this->em->flush($property);
                $responseArray[] = array(
                    'result_message' => "Successfully Updated Property terms",
                    'result_code' => 0
                );
            } else {
                $responseArray[] = array(
                    'result_message' => "Property not found",
                    'result_code' => 1
                );
            }

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function contactUs($guestName, $email, $phoneNumber, $message, $request): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
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
                    array("id" => $_SESSION['PROPERTY_ID']));
            }


            if ($property != null) {
                $emailPrefix = "Message from $guestName\r\n Phone: $phoneNumber \r\nEmail: $email";
                $headers = 'From:' . $property->getEmailAddress() . "\r\n" .
                    'Reply-To: ' . $email . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
                $whitelist = array('localhost', '::1' );
                // check if the server is in the array
                if ( !in_array( $_SERVER['REMOTE_ADDR'], $whitelist ) ) {
                    mail($property->getEmailAddress(), "Website - Message from guest", $emailPrefix . $message, $headers);
                    $this->logger->debug("Successfully sent email to guest");
                }else{
                    $this->logger->debug("local server email not sent");
                }

                $responseArray[] = array(
                    'result_message' => 'Successfully sent message. Thank you',
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
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }
}