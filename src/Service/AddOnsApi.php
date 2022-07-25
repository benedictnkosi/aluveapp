<?php

namespace App\Service;

use App\Entity\AddOns;
use App\Entity\Property;
use App\Entity\ReservationAddOns;
use App\Entity\Reservations;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class AddOnsApi
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

    public function getAddOn($addOnName, $propertyUid)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $securityApi = new SecurityApi($this->em, $this->logger);
            if (!$securityApi->isLoggedInBoolean($propertyUid)) {
                $responseArray[] = array(
                    'result_message' => "Session expired, please logout and login again",
                    'result_code' => 1
                );
            } else {
                $propertyApi = new PropertyApi($this->em, $this->logger);
                $propertyId =   $propertyApi->getPropertyIdByUid($propertyUid);
                return $this->em->getRepository(AddOns::class)->findOneBy(
                    array("name" => $addOnName,
                        'property' => $propertyId));
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

    public function getAddOns($propertyUid)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $propertyApi = new PropertyApi($this->em, $this->logger);
            $propertyId =   $propertyApi->getPropertyIdByUid($propertyUid);
            return $this->em->getRepository(AddOns::class)->findBy(array('property' => $propertyId, 'status'=> 'live'));
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info(print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return null;
    }

    public function getAddOnsJson($addOnId): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $addOn = $this->em->getRepository(AddOns::class)->findOneBy(array('id' => $addOnId));
            if($addOn === null){
                $responseArray[] = array(
                    'result_message' => "Add on not found for id $addOnId",
                    'result_code' => 1
                );
            }else{
                $responseArray[] = array(
                    'id' => $addOn->getId(),
                    'name' => $addOn->getName(),
                    'price' => $addOn->getPrice(),
                    'property' => $addOn->getProperty()->getId(),
                    'status' => $addOn->getStatus(),
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

    public function getReservationAddOns($resId): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $addOns = $this->em->getRepository(ReservationAddOns::class)->findBy(array('reservation' => $resId));
            $this->logger->info("no errors finding add ons for reservation $resId. add on count " . count($addOns));
            return $addOns;
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info("failed to get add ons " . print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function addAdOnToReservation($resId, $adOnId, $quantity): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $addOn = $this->em->getRepository(AddOns::class)->findOneBy(array('id' => intval($adOnId)));
            if ($addOn == null) {
                $responseArray[] = array(
                    'result_message' => "Please select a valid add on item",
                    'result_code' => 1
                );
                return $responseArray;
            }
            $reservation = $this->em->getRepository(Reservations::class)->findOneBy(array('id' => intval($resId)));
            $now = new DateTime('today midnight');

            $resAddOn = new ReservationAddOns();
            $resAddOn->setAddOn($addOn);
            $resAddOn->setQuantity(intval($quantity));
            $resAddOn->setReservation($reservation);
            $resAddOn->setDate($now);

            $this->em->persist($resAddOn);
            $this->em->flush($resAddOn);

            $responseArray[] = array(
                'result_message' => 'Successfully added add on to the reservation',
                'result_code' => 0
            );
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

    public function updateAddOn($addOnId, $field, $newValue)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $addOn = $this->em->getRepository(AddOns::class)->findOneBy(array("id" => $addOnId));
            if ($addOn === null) {
                $responseArray[] = array(
                    'result_message' => "Addon not found",
                    'result_code' => 1
                );
                $this->logger->info(print_r($responseArray, true));
            } else {
                switch ($field) {
                    case "price":
                        $addOn->setPrice($newValue);
                        break;
                    case "name":
                        $addOn->setName($newValue);
                        break;
                    default:
                        $responseArray[] = array(
                            'result_message' => "field not found",
                            'result_code' => 1
                        );
                        break;
                }
                $this->em->persist($addOn);
                $this->em->flush($addOn);

                $responseArray[] = array(
                    'result_message' => "Successfully updated add-on",
                    'result_code' => 0
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

    public function deleteAddOn($addOnId)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $addOn = $this->em->getRepository(AddOns::class)->findOneBy(array("id" => $addOnId));
            if ($addOn === null) {
                $responseArray[] = array(
                    'result_message' => "Addon not found",
                    'result_code' => 1
                );
                $this->logger->info(print_r($responseArray, true));
            } else {
                $addOn->setStatus("deleted");
                $this->em->persist($addOn);
                $this->em->flush();
                $responseArray[] = array(
                    'result_message' => "Successfully deleted add-on",
                    'result_code' => 0
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

    public function createAddOn($addOnName, $addOnPrice, $propertyUid)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $this->logger->info("attempting to talk to db");
            //check if add-on with the same name does not exist
            $existingAddOn = $this->em->getRepository(AddOns::class)->findBy(array('name' => $addOnName));
            $this->logger->info("db connect done success");
            if ($existingAddOn != null) {
                $responseArray[] = array(
                    'result_message' => "Add on with the same name already exists",
                    'result_code' => 1
                );
            } else {
                $property = $this->em->getRepository(Property::class)->findOneBy(array('uid' => $propertyUid));
                $addOn = new AddOns();
                $addOn->setPrice($addOnPrice);
                $addOn->setName($addOnName);
                $addOn->setProperty($property);
                $this->em->persist($addOn);
                $this->em->flush($addOn);
                $responseArray[] = array(
                    'result_message' => "Successfully created add on",
                    'result_code' => 0,
                    'add_on_id' => $addOn->getId()
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

    public function getAddOnsForInvoice($resId)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $html = "";
        try {
            $addOns = $this->em->getRepository(ReservationAddOns::class)->findBy(array('reservation' => $resId));
            $this->logger->info("number of add ons " . count($addOns));
            foreach ($addOns as $addOn) {
                $totalPriceForAllAdOns = (intVal($addOn->getAddOn()->getPrice()) * intval($addOn->getQuantity()));
                $html .= '<tr class="item">
					<td>' . $addOn->getAddOn()->getName() . '</td>
					<td>' . $addOn->getQuantity() . '</td>
					<td>R' . number_format((float)$addOn->getAddOn()->getPrice(), 2, '.', '') . '</td>
					<td>R' . number_format((float)$totalPriceForAllAdOns, 2, '.', '') . '</td>
				</tr>';
            }
            $this->logger->info($html);
            return $html;
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            return $html;
        }
    }

    public function getAddOnsTotal($resId): float|int
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $html = "";
        try {
            $addOns = $this->em->getRepository(ReservationAddOns::class)->findBy(array('reservation' => $resId));
            $totalPriceForAllAdOns = 0;
            foreach ($addOns as $addOn) {
                $totalPriceForAllAdOns += (intVal($addOn->getAddOn()->getPrice()) * intval($addOn->getQuantity()));
            }
            $this->logger->info($html);
            return $totalPriceForAllAdOns;
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            return 0;
        }
    }
}