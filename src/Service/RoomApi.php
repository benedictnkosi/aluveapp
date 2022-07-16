<?php

namespace App\Service;

use App\Entity\Property;
use App\Entity\ReservationStatus;
use App\Entity\RoomBedSize;
use App\Entity\RoomImages;
use App\Entity\Rooms;
use App\Entity\RoomStatus;
use App\Helpers\FormatHtml\RoomImagesHTML;
use Exception;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

class RoomApi
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function getAvailableRooms($checkInDate, $checkOutDate): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            if(!isset($_SESSION['PROPERTY_ID'])) {
                $responseArray[] = array(
                    'result_message' => 'Property ID not set, please logout and login again',
                    'result_code'=> 1
                );
            }else{
                $rooms = $this->em->getRepository(Rooms::class)->findBy(array('property'=>$_SESSION['PROPERTY_ID']));
                foreach ($rooms as $room) {
                    if ($this->isRoomAvailable($room->getId(), $checkInDate, $checkOutDate)) {
                        $responseArray[] = $room;
                    }
                }
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

    public function isRoomAvailable($roomId, $checkInDate, $checkOutDate, $reservationToExclude = 0): bool|array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        $returnValue = false;
        try {
            $status =  $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));
            $reservations = $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
            WHERE 
            (
            (r.checkOut > '" . $checkInDate . "' and r.checkIn <=  '" . $checkInDate . "') 
            or
            (r.checkIn < '" . $checkOutDate . "' and r.checkIn >  '" . $checkInDate . "') 
            )
            And r.status = ".$status->getId()."
            And r.room = $roomId
            And r.id != $reservationToExclude")
                ->getResult();

            if (count($reservations) < 1) {
                $returnValue = true;
                $this->logger->info("No reservations found");
            } else {
                $this->logger->info("reservations found");
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_code' => 1,
                'result_message' => $ex->getMessage()
            );
            $this->logger->info(print_r($responseArray, true));
            return $responseArray;
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $returnValue;
    }

    public function getRooms($roomId): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            if(!isset($_SESSION['PROPERTY_ID'])) {
                $responseArray[] = array(
                    'result_message' => 'Property ID not set, please logout and login again',
                    'result_code'=> 1
                );
            }else{
                if(strcmp($roomId, "all") === 0 ){
                    $rooms = $this->em->getRepository(Rooms::class)->findBy(array('property'=>$_SESSION['PROPERTY_ID']));
                }else{
                    $rooms = $this->em->getRepository(Rooms::class)->findBy(array('id' => $roomId));
                }


                if(count($rooms)<1){
                    $responseArray[] = array(
                        'result_message' => "Rooms not found for room id $roomId",
                        'result_code' => 1
                    );
                    $this->logger->info("No rooms found");
                }else{
                    foreach ($rooms as $item) {
                        $linkedRoom = $item->getLinkedRoom();
                        $linkedRoomId = 0;
                        if (strlen($linkedRoom) > 0) {
                            $linkedRoomId = $item->getLinkedRoom();
                        }

                        $stairs = 0;
                        if($item->getStairs() === true){
                            $stairs = 1;
                        }

                        $roomImages = $this->getRoomImages($roomId);
                        $roomImagesUploadHtml = new RoomImagesHTML($this->em, $this->logger);
                        $imagesHtml = $roomImagesUploadHtml->formatUpdateRoomHtml($roomImages);

                        $responseArray[] = array(
                            'id' => $item->GetId(),
                            'name' => $item->GetName(),
                            'price' => $item->GetPrice(),
                            'status' => $item->GetStatus()->getId(),
                            'sleeps' => $item->GetSleeps(),
                            'description' => $item->getDescription(),
                            'bed' => $item->getBed()->getId(),
                            'stairs' => $stairs,
                            'linked_room' => $linkedRoomId,
                            'room_size' => $item->getSize(),
                            'uploaded_images' => $imagesHtml,
                            'result_code' => 0
                        );
                    }
                }
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

    public function getRoom($roomId)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            return $this->em->getRepository(Rooms::class)->findOneBy(array('id' => $roomId));
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

    public function getRoomsEntities($roomId = 0): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            if(!isset($_SESSION['PROPERTY_ID'])) {
                $responseArray[] = array(
                    'result_message' => 'Property ID not set, please logout and login again',
                    'result_code'=> 1
                );
            }else{
                if ($roomId == 0) {
                    $rooms = $this->em->getRepository(Rooms::class)->findBy(array('property'=>$_SESSION['PROPERTY_ID']));
                } else {
                    $rooms = $this->em->getRepository(Rooms::class)->findBy(array('id' => $roomId));
                }
                $this->logger->info("Ending Method before the return: " . __METHOD__);
                return $rooms;
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

    public function getRoomImages($roomId): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $room = $this->em->getRepository(Rooms::class)->findOneBy(
                array('id' => $roomId));
            if ($room === null) {
                $this->logger->info("room is null");
                $responseArray[] = array(
                    'result_message' => "room is null",
                    'result_code' => 1
                );
                return $responseArray;
            }

            //get room images
            $roomImages = $this->em->getRepository(RoomImages::class)->findBy(
                array('room' => $roomId,
                    'status'=> array("active","default")));

            $this->logger->info("Ending Method before the return: " . __METHOD__);
            return $roomImages;
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

    public function addImageToRoom($imageName, Rooms $room){

        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $roomImage = new RoomImages();

            //check if its the first image for the room, if so make it default
            $roomImages = getRoomImages($room->getId());
            if(count($roomImages)<1){
                $roomImage->setStatus("default");
            }else{
                $roomImage->setStatus("active");
            }

            $roomImage->setName($imageName);
            $roomImage->setRoom($room);

            $this->em->persist($roomImage);
            $this->em->flush($roomImage);

            $responseArray[] = array(
                'result_message' => "Successfully linked image to the room",
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

    public function getRoomStatuses(): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $roomStatuses = $this->em->getRepository(RoomStatus::class)->findAll();
            $this->logger->info("Ending Method before the return: " . __METHOD__);
            return $roomStatuses;
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

    public function getRoomBedSizes(): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $roomBedSizes = $this->em->getRepository(RoomBedSize::class)->findAll();
            $this->logger->info("Ending Method before the return: " . __METHOD__);
            return $roomBedSizes;
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

    public function updateCreateRoom($id, $name, $price, $sleeps, $status, $linkedRoom, $size, $bed, $stairs, $description): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            if(!isset($_SESSION['PROPERTY_ID'])) {
                $responseArray[] = array(
                    'result_message' => 'Property ID not set, please logout and login again',
                    'result_code'=> 1
                );
            }else{
                $room = $this->em->getRepository(Rooms::class)->findOneBy(array('id' => $id));
                if ($room == null) {
                    $room = new Rooms();
                    $successMessage = "Successfully created room";
                }else{
                    $successMessage = "Successfully updated room";
                }
                $bedSize = $this->em->getRepository(RoomBedSize::class)->findOneBy(array('id' => $bed));
                $roomStatus = $this->em->getRepository(RoomStatus::class)->findOneBy(array('id' => $status));

                if(strlen($linkedRoom)>1){
                    $room->setLinkedRoom($linkedRoom);
                }

                $property = $this->em->getRepository(Property::class)->findOneBy(array('id'=>$_SESSION['PROPERTY_ID']));

                $room->setName($name);
                $room->setPrice($price);
                $room->setSleeps($sleeps);
                $room->setStatus($roomStatus);
                $room->setLinkedRoom($linkedRoom);
                $room->setSize($size);
                $room->setBed($bedSize);
                $room->setStairs($stairs);
                $room->setDescription($description);
                $room->setProperty($property);

                $this->em->persist($room);
                $this->em->flush($room);

                $responseArray[] = array(
                    'result_message' => $successMessage,
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

    public function removeImage($imageId): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            //get room images
            $roomImage = $this->em->getRepository(RoomImages::class)->findOneBy(array('id' => $imageId));

            if($roomImage === null){
                $responseArray[] = array(
                    'result_message' => "image not found",
                    'result_code' => 1
                );
            }else{
                if(strcmp($roomImage->getStatus(),'default')===0){
                    $responseArray[] = array(
                        'result_message' => "Can not delete image as it is set as the default image",
                        'result_code' => 1
                    );
                }else{
                    $roomImage->setStatus("removed");
                    $this->em->persist($roomImage);
                    $this->em->flush($roomImage);

                    $responseArray[] = array(
                        'result_message' => "Successfully removed image",
                        'result_code' => 0
                    );
                }
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

    public function markDefault($imageId): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            //get room images
            $roomImage = $this->em->getRepository(RoomImages::class)->findOneBy(array('id' => $imageId));

            if($roomImage === null){
                $responseArray[] = array(
                    'result_message' => "image not found",
                    'result_code' => 1
                );
            }else{
                //remove the current default
                $roomDefaultImage = $this->em->getRepository(RoomImages::class)->findOneBy(array('status' => 'default', 'room'=>$roomImage->getRoom()->getId()));
                if($roomDefaultImage != null){
                    $roomDefaultImage->setStatus("active");
                    $this->em->persist($roomDefaultImage);
                    $this->em->flush($roomDefaultImage);
                }

                $roomImage->setStatus("default");
                $this->em->persist($roomImage);
                $this->em->flush($roomImage);

                $responseArray[] = array(
                    'result_message' => "Successfully removed image",
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

}