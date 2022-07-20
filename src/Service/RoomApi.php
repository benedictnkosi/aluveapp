<?php

namespace App\Service;

use App\Entity\Property;
use App\Entity\ReservationStatus;
use App\Entity\RoomBedSize;
use App\Entity\RoomImages;
use App\Entity\Rooms;
use App\Entity\RoomStatus;
use App\Entity\RoomTv;
use App\Helpers\FormatHtml\RoomImagesHTML;
use Exception;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class RoomApi
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;

        if (session_id() === '') {
            $logger->info("Session id is empty");
            $session = new Session();
            $session->start();
        }
    }

    public function getAvailableRooms($checkInDate, $checkOutDate, $propertyUid): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $propertyApi = new PropertyApi($this->em, $this->logger);
            $propertyId = $propertyApi->getPropertyIdByUid($propertyUid);
            $rooms = $this->em->getRepository(Rooms::class)->findBy(array('property' => $propertyId));
            foreach ($rooms as $room) {
                if ($this->isRoomAvailable($room->getId(), $checkInDate, $checkOutDate)) {
                    $responseArray[] = $room;
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
            $status = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));
            $reservations = $this->em
                ->createQuery("SELECT r FROM App\Entity\Reservations r 
            WHERE 
            (
            (r.checkOut > '" . $checkInDate . "' and r.checkIn <=  '" . $checkInDate . "') 
            or
            (r.checkIn < '" . $checkOutDate . "' and r.checkIn >  '" . $checkInDate . "') 
            )
            And r.status = " . $status->getId() . "
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

    public function getRooms($roomId, $propertyUid = null): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            if (strcmp($roomId, "all") === 0) {
                //check if the PROPERTY_ID if not get it from the host
                $propertyApi = new PropertyApi($this->em, $this->logger);
                $propertyId =   $propertyApi->getPropertyIdByUid($propertyUid);
                $rooms = $this->em->getRepository(Rooms::class)->findBy(array('property' => $propertyId));
            } else {
                $rooms = $this->em->getRepository(Rooms::class)->findBy(array('id' => $roomId));
            }


            if (count($rooms) < 1) {
                $responseArray[] = array(
                    'result_message' => "Rooms not found for room id $roomId",
                    'result_code' => 1
                );
                $this->logger->info("No rooms found");
            } else {
                foreach ($rooms as $room) {
                    $linkedRoom = $room->getLinkedRoom();
                    $linkedRoomId = 0;
                    if (strlen($linkedRoom) > 0) {
                        $linkedRoomId = $room->getLinkedRoom();
                    }

                    $stairs = 0;
                    if ($room->getStairs() === true) {
                        $stairs = 1;
                    }

                    $roomImages = $this->getRoomImages($roomId);
                    $roomImagesUploadHtml = new RoomImagesHTML($this->em, $this->logger);
                    $imagesHtml = $roomImagesUploadHtml->formatUpdateRoomHtml($roomImages);

                    $responseArray[] = array(
                        'id' => $room->GetId(),
                        'name' => $room->GetName(),
                        'price' => $room->GetPrice(),
                        'status' => $room->GetStatus()->getId(),
                        'sleeps' => $room->GetSleeps(),
                        'description' => $room->getDescription(),
                        'bed' => $room->getBed()->getId(),
                        'bed_name' => $room->getBed()->getName(),
                        'stairs' => $stairs,
                        'linked_room' => $linkedRoomId,
                        'room_size' => $room->getSize(),
                        'uploaded_images' => $imagesHtml,
                        'tv' => $room->getTv()->getName(),
                        'result_code' => 0
                    );

                    $_SESSION['room_id'] = $room->GetId();
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

    public function getRoomsEntities($propertyUid, $roomId = 0,): ?array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        try {
            if ($roomId === 0) {
                $propertyApi = new PropertyApi($this->em, $this->logger);
                $propertyId = $propertyApi->getPropertyIdByUid($propertyUid);
                if (!isset($propertyUid)) {
                    $this->logger->info("PROPERTY_ID not found in session. checking if the host has a property id" . __METHOD__);

                    if ($propertyId === null) {
                        $responseArray[] = array(
                            'result_message' => 'Property ID not set, please logout and login again',
                            'result_code' => 1
                        );
                        $this->logger->info(print_r($responseArray, true));
                        return null;
                    } else {
                        $rooms = $this->em->getRepository(Rooms::class)->findBy(array('property' => $propertyId));
                    }
                } else {
                    $rooms = $this->em->getRepository(Rooms::class)->findBy(array('property' => $propertyId));
                }

            } else {
                $rooms = $this->em->getRepository(Rooms::class)->findBy(array('id' => $roomId));
            }
            $this->logger->info("Ending Method before the return: " . __METHOD__);
            return $rooms;
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info(print_r($responseArray, true));
            return null;
        }
    }

    public function getRoomImages($roomId): ?array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $room = $this->em->getRepository(Rooms::class)->findOneBy(
                array('id' => $roomId));
            if ($room === null) {
                $this->logger->info("room is null");
                return null;
            }

            //get room images
            $roomImages = $this->em->getRepository(RoomImages::class)->findBy(
                array('room' => $roomId,
                    'status' => array("active", "default")));

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

    public function addImageToRoom($imageName, $roomId)
    {

        $this->logger->info("Starting Method: " . __METHOD__);

        try {
            $roomImage = new RoomImages();
            $room = $this->getRoom($roomId);
            if ($room === null) {
                $responseArray[] = array(
                    'result_message' => "Room not found",
                    'result_code' => 1
                );
                return $responseArray;
            }
            //check if its the first image for the room, if so make it default
            $roomImages = $this->getRoomImages($room->getId());
            if (count($roomImages) < 1) {
                $roomImage->setStatus("default");
            } else {
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

    public function getRoomTvs(): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $roomTvs = $this->em->getRepository(RoomTv::class)->findAll();
            $this->logger->info("Ending Method before the return: " . __METHOD__);
            return $roomTvs;
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

    public function updateCreateRoom($id, $name, $price, $sleeps, $status, $linkedRoom, $size, $bed, $stairs, $tv, $description, $propertyUid): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $room = $this->em->getRepository(Rooms::class)->findOneBy(array('id' => $id));
            if ($room == null) {
                $room = new Rooms();
                $successMessage = "Successfully created room";
            } else {
                $successMessage = "Successfully updated room";
            }
            $bedSize = $this->em->getRepository(RoomBedSize::class)->findOneBy(array('id' => $bed));
            $tvType = $this->em->getRepository(RoomTv::class)->findOneBy(array('id' => $tv));
            $roomStatus = $this->em->getRepository(RoomStatus::class)->findOneBy(array('id' => $status));

            if (strlen($linkedRoom) > 1) {
                $room->setLinkedRoom($linkedRoom);
            }
            $propertyApi = new PropertyApi($this->em, $this->logger);
            $propertyId = $propertyApi->getPropertyIdByUid($propertyUid);
            $property = $this->em->getRepository(Property::class)->findOneBy(array('id' => $propertyId));

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
            $room->setTv($tvType);

            $this->em->persist($room);
            $this->em->flush($room);

            $responseArray[] = array(
                'result_message' => $successMessage,
                'result_code' => 0,
                'room_id' => $room->getId()
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

    public function removeImage($imageId): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            //get room images
            $roomImage = $this->em->getRepository(RoomImages::class)->findOneBy(array('id' => $imageId));

            if ($roomImage === null) {
                $responseArray[] = array(
                    'result_message' => "image not found",
                    'result_code' => 1
                );
            } else {
                if (strcmp($roomImage->getStatus(), 'default') === 0) {
                    $responseArray[] = array(
                        'result_message' => "Can not delete image as it is set as the default image",
                        'result_code' => 1
                    );
                } else {
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

            if ($roomImage === null) {
                $responseArray[] = array(
                    'result_message' => "image not found",
                    'result_code' => 1
                );
            } else {
                //remove the current default
                $roomDefaultImage = $this->em->getRepository(RoomImages::class)->findOneBy(array('status' => 'default', 'room' => $roomImage->getRoom()->getId()));
                if ($roomDefaultImage != null) {
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