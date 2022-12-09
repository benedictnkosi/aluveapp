<?php

namespace App\Service;

use App\Entity\Property;
use App\Entity\Reservations;
use App\Helpers\SMSHelper;
use Exception;
use phpDocumentor\Reflection\Types\Void_;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Guest;

class GuestApi
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        if (session_id() === '') {
            $logger->info("Session id is empty");
            session_start();
        }
    }

    public function createGuest($name, $phoneNumber, $email,  $origin, $propertyId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $property = $this->em->getRepository(Property::class)->findOneBy(array('id' => $propertyId));
            $guest = new Guest();
            $guest->setName($name);
            $guest->setPhoneNumber(str_replace("+27", "0", trim($phoneNumber)));
            $guest->setEmail($email);
            $guest->setProperty($property);
            $guest->setComments($origin);

            $this->em->persist($guest);
            $this->em->flush($guest);
            $responseArray[] = array(
                'result_code' => 0,
                'result_message' => 'Successfully created guest',
                'guest' => $guest
            );

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_code' => 1,
                'result_message' => $ex->getMessage() .' - '. __METHOD__ . ':' . $ex->getLine() . ' ' .  $ex->getTraceAsString(),
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function updateGuestPhoneNumber($guestId, $phoneNumber): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $guest = $this->em->getRepository(Guest::class)->findOneBy(array('id' => $guestId ));
            if($guest === null){
                $responseArray[] = array(
                    'result_code' => 1,
                    'result_message' => 'Guest not found for id ' . $guestId
                );
            }else{
                $guest->setPhoneNumber($phoneNumber);
                $this->em->persist($guest);
                $this->em->flush($guest);
                $responseArray[] = array(
                    'result_code' => 0,
                    'result_message' => 'Successfully updated guest phone number'
                );
            }

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_code' => 1,
                'result_message' => $ex->getMessage() .' - '. __METHOD__ . ':' . $ex->getLine() . ' ' .  $ex->getTraceAsString(),
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function updateGuestEmail($guestId, $email): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $guest = $this->em->getRepository(Guest::class)->findOneBy(array('id' => $guestId ));
            if($guest === null){
                $responseArray[] = array(
                    'result_code' => 1,
                    'result_message' => 'Guest not found for id ' . $guestId
                );
            }else{
                $guest->setEmail($email);
                $this->em->persist($guest);
                $this->em->flush($guest);
                $responseArray[] = array(
                    'result_code' => 0,
                    'result_message' => 'Successfully updated guest email address'
                );
            }

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_code' => 1,
                'result_message' => $ex->getMessage() .' - '. __METHOD__ . ':' . $ex->getLine() . ' ' .  $ex->getTraceAsString(),
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function updateGuestIdNumber($guestId, $IdNumber): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            //check if ID not linked to a blocked guest
            $blockedGuest = $this->em->getRepository(Guest::class)->findOneBy(array('idNumber' => $IdNumber, 'state' => 'blocked'));
            if($blockedGuest !== null) {
                $responseArray[] = array(
                    'result_code' => 1,
                    'result_message' => 'This ID number was blocked for ' . $blockedGuest->getComments() . ". ID is linked to number " . $blockedGuest->getPhoneNumber()
                );
                return $responseArray;
            }

            $guest = $this->em->getRepository(Guest::class)->findOneBy(array('id' => $guestId ));
            if($guest === null) {
                $responseArray[] = array(
                    'result_code' => 1,
                    'result_message' => 'Guest not found for id ' . $guestId
                );
            }else{
                $guest->setIdNumber($IdNumber);
                $this->em->persist($guest);
                $this->em->flush($guest);
                $responseArray[] = array(
                    'result_code' => 0,
                    'result_message' => 'Successfully updated guest ID number'
                );
            }

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_code' => 1,
                'result_message' => $ex->getMessage() .' - '. __METHOD__ . ':' . $ex->getLine() . ' ' .  $ex->getTraceAsString(),
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function createAirbnbGuest($confirmationCode, $name): ?array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            //get property id
            $reservation = $this->em->getRepository(Reservations::class)->findOneBy(array('originUrl' => $confirmationCode));
            if($reservation === null){
                $this->logger->debug("Reservation not found");
                return null;
            }

            $property = $reservation->getRoom()->getProperty();

            $guest = $this->em->getRepository(Guest::class)->findOneBy(array('name' => $name,
                'property' => $property->getId(),
                'comments' => 'airbnb'));

            if($guest === null){
                $guest = new Guest();
                $guest->setName($name);
                $guest->setComments('airbnb');
                $guest->setProperty($property);
                $this->em->persist($guest);
                $this->em->flush($guest);
            }

            $reservation->setGuest($guest);

            $this->em->persist($reservation);
            $this->em->flush($reservation);
            $responseArray[] = array(
                'result_code' => 0,
                'result_message' => 'Successfully updated reservation guest'
            );
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_code' => 1,
                'result_message' => $ex->getMessage() .' - '. __METHOD__ . ':' . $ex->getLine() . ' ' .  $ex->getTraceAsString(),
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function blockGuest($reservationId, $reason): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $reservation = $this->em->getRepository(Reservations::class)->findOneBy(array('id' => $reservationId));

            $guest = $reservation->getGuest();
            $guest->setState("blocked");
            $guest->setComments($reason);
            $this->em->persist($guest);
            $this->em->flush($guest);
            $responseArray[] = array(
                'result_code' => 0,
                'result_message' => 'Successfully blocked guest'
            );
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_code' => 1,
                'result_message' => $ex->getMessage() .' - '. __METHOD__ . ':' . $ex->getLine() . ' ' .  $ex->getTraceAsString(),
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function getGuests($filterValue): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            $propertyId =   $_SESSION['PROPERTY_ID'];
            if ($filterValue == 0) {
                $guest = $this->em->getRepository(Guest::class)->findBy(array('property' => $propertyId));
            } else {
                if (strlen($filterValue) > 4) {
                    $guest = $this->em->getRepository(Guest::class)->findOneBy(array('phoneNumber' => str_replace("+27", "0", trim($filterValue)), 'property' => $propertyId));
                } else {
                    $guest = $this->em->getRepository(Guest::class)->findOneBy(array('id' => $filterValue, 'property' => $propertyId));
                }
            }
            $responseArray = array();

            if($guest === null){
                $responseArray[] = array(
                    'result_code' => 1
                );
            }else{
                $responseArray[] = array(
                    'id' => $guest->getId(),
                    'name' => $guest->getName(),
                    'image_id' => $guest->getIdImage(),
                    'phone_number' => $guest->getPhoneNumber(),
                    'email' => $guest->getEmail(),
                    'state' => $guest->getState(),
                    'comments' => $guest->getComments(),
                    'id_number' => $guest->getIdNumber(),
                    'result_code' => 0
                );
            }
        } catch (Exception $exception) {
            $responseArray[] = array(
                'result_message' => $exception->getMessage(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function getGuestByPhoneNumber($phoneNumber, $request)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $guest = null;
        $responseArray = array();
        try {
            $propertyApi = new PropertyApi($this->em, $this->logger);
            $propertyId = $propertyApi->getPropertyIdByHost($request);
            $guest = $this->em->getRepository(Guest::class)->findOneBy(array('phoneNumber' => str_replace("+27", "0", trim($phoneNumber)), 'property' => $propertyId));
        } catch (Exception $exception) {
            $responseArray[] = array(
                'result_message' => $exception->getMessage(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $guest;
    }

    public function getGuestByName($name)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $guest = null;
        $responseArray = array();
        try {
            $propertyId =   $_SESSION['PROPERTY_ID'];
            $guest = $this->em->getRepository(Guest::class)->findOneBy(array('name' => $name, 'property' => $propertyId));
        } catch (Exception $exception) {
            $responseArray[] = array(
                'result_message' => $exception->getMessage(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $guest;
    }

    public function getGuestById($guestId)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $guest = null;
        $responseArray = array();
        try {
            $guest = $this->em->getRepository(Guest::class)->findOneBy(array('id' => $guestId));
        } catch (Exception $exception) {
            $responseArray[] = array(
                'result_message' => $exception->getMessage(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $guest;
    }

    function startsWith($haystack, $needle): bool
    {
        $length = strlen($needle);
        return substr($haystack, 0, $length) === $needle;
    }

    public function getGuestStaysCount($guestId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $stays = $this->em->getRepository(Reservations::class)->findBy(array('guest' => $guestId,
                'status' => 'confirmed'));
            $responseArray[] = array(
                'result_message' => count($stays),
                'result_code' => 0
            );
        } catch (Exception $exception) {
            $responseArray = array(
                'result_message' => $exception->getMessage(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }
        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function getGuestPreviousRooms($guestId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);

        $responseArray = array();
        try {
            $reservations = $this->em->getRepository(Reservations::class)->findBy(array('guest' => $guestId,
                'status' => 'confirmed'));
            foreach ($reservations as $item) {
                $responseArray[] = array(
                    'rooms' => $item->getRoom(),
                    'result_code' => 0
                );
            }
        } catch (Exception $exception) {
            $responseArray[] = array(
                'result_message' => $exception->getMessage(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function hasGuestStayedInRoom($guestId, $roomId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);

        $responseArray = array();
        try {
            $guestPreviousRooms = $this->getGuestPreviousRooms($guestId);
            foreach ($guestPreviousRooms as $room) {
                if ($room->getId() == $roomId) {
                    $responseArray[] = array(
                        'result_message' => true,
                        'result_code' => 0
                    );
                }
            }
        } catch (Exception $exception) {
            $responseArray[] = array(
                'result_message' => $exception->getMessage(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function sendBookDirectSMS($guestId)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $guest = null;
        $responseArray = array();
        try {
            $guest = $this->em->getRepository(Guest::class)->findOneBy(array('id' => $guestId));
            //get room price
            $reservationApi = new ReservationApi($this->em, $this->logger);

            $reservation = $reservationApi->getReservationsByGuest($guestId);
            $roomPrice = $reservation->getRoom()->getPrice();

            $SMSHelper = new SMSHelper($this->logger);
            $message = "Aluve Guesthouse got your number :)  Book directly with us and pay only R$roomPrice per night. Book online aluvegh.co.za or call +27796347610.";
            $SMSHelper->sendMessage($guest->getPhoneNumber(), $message);

        } catch (Exception $exception) {
            $responseArray[] = array(
                'result_message' => $exception->getMessage(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $guest;
    }


}