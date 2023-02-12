<?php

namespace App\Service;

use App\Entity\BlockedRooms;
use App\Entity\Reservations;
use DateInterval;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

class BlockedRoomApi
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        if(session_id() === ''){
            $logger->info("Session id is empty". __METHOD__ );
            session_start();
        }
    }

    public function blockRoom($roomId, $fromDate,$toDate , $comments, $reservationId = null): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $this->logger->debug("blocking room: " . $roomId);

        $responseArray = array();
        try {
            //get the room
            $roomApi = new RoomApi( $this->em, $this->logger);
            $room = $roomApi->getRoom($roomId);
            if($room == null){
                $responseArray[] = array(
                    'result_code' => 1,
                    'result_message' => "Room not found for id $roomId"
                );
                $this->logger->debug("Ending Method before the return: " . __METHOD__);
                return $responseArray;
            }else{
                $this->logger->debug("Room is not null");
            }

            $date = new DateTime();
            $toDateDateTime = new DateTime($toDate);
            $fromDateDateTime = new DateTime($fromDate);

            //check if there is a room blocked for reservation
            if($reservationId !== null){
                $comments .= "reservation - $reservationId";
                $blockRoom = $this->em->getRepository(BlockedRooms::class)->findOneBy(array('linkedResaId' => $reservationId));
                if($blockRoom === null){
                    $blockRoom = new BlockedRooms();
                }
            }else{
                $blockRoom = new BlockedRooms();
            }

            $blockRoom->setRoom($room);
            $blockRoom->setComment($comments);
            $blockRoom->setFromDate($fromDateDateTime);
            $blockRoom->setToDate($toDateDateTime);
            $blockRoom->setCreatedDate($date);
            $blockRoom->setLinkedResaId($reservationId);
            $blockRoom->setUid(uniqid() . "@" . SERVER_NAME);
            $this->em->persist($blockRoom);
            $this->em->flush($blockRoom);

            $responseArray[] = array(
                'result_code' => 0,
                'result_message' => 'Successfully blocked room',
                'block_id' => $blockRoom->getId()
            );
            $this->logger->debug(print_r($responseArray, true));
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

    public function getBlockedRoomsByRoomId($roomId)
    {
        $this->logger->debug("Starting Method: " . __METHOD__ );
        $responseArray = array();
        try{
            $now = new DateTime('today midnight');

            $blockedRooms = $this->em
                ->createQuery("SELECT b FROM App\Entity\BlockedRooms b 
            JOIN b.room r
            WHERE b.room = r.id
            and b.toDate >= '".$now->format('Y-m-d')."' 
            and b.room = $roomId 
            order by b.fromDate asc ")
                ->getResult();

            $this->logger->debug("Ending Method before the return: " . __METHOD__ );
            return $blockedRooms;
        }catch(Exception $exception){
            $responseArray[] = array(
                'result_message' => $exception->getMessage() . " - " . $exception->getTraceAsString(),
                'result_code'=> 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__ );
        return null;
    }

    public function getBlockedRoomsByProperty()
    {
        $this->logger->debug("Starting Method: " . __METHOD__ );
        $responseArray = array();
        try{
            $now = new DateTime('today midnight');

            $blockedRooms = $this->em
                ->createQuery("SELECT b FROM App\Entity\BlockedRooms b 
            JOIN b.room r
                JOIN r.property p
            WHERE b.room = r.id
            and p.id = r.property
            and p.id = ".$_SESSION['PROPERTY_ID']."
            and b.toDate >= '".$now->format('Y-m-d')."' 
            order by b.fromDate asc ")
                ->getResult();


            $this->logger->debug("Ending Method before the return: " . __METHOD__ );
            return $blockedRooms;
        }catch(Exception $exception){
            $responseArray[] = array(
                'result_message' => $exception->getMessage() . " - " . $exception->getTraceAsString(),
                'result_code'=> 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__ );
        return null;
    }

    public function deleteBlockedRoom($blockedRoomId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__ );
        $responseArray = array();
        try{
            $blockedRoom = $this->em->getRepository(BlockedRooms::class)->findOneBy(array('id' => $blockedRoomId));
            $this->em->remove($blockedRoom);
            $this->em->flush();
            $responseArray[] = array(
                'result_message' => "Successfully deleted blocked room",
                'result_code'=> 0
            );
        }catch(Exception $exception){
            $responseArray[] = array(
                'result_message' => $exception->getMessage(),
                'result_code'=> 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__ );
        return $responseArray;
    }

    public function deleteBlockedRoomByReservation($reservationId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__ );
        $responseArray = array();
        try{
            $blockedRoom = $this->em->getRepository(BlockedRooms::class)->findOneBy(array('linkedResaId' => $reservationId));
            if($blockedRoom != null){
                $this->em->remove($blockedRoom);
                $this->em->flush();
                $responseArray[] = array(
                    'result_message' => "Successfully deleted blocked room",
                    'result_code'=> 0
                );
            }
        }catch(Exception $exception){
            $responseArray[] = array(
                'result_message' => $exception->getMessage(),
                'result_code'=> 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__ );
        return $responseArray;
    }

    public function updateBlockedRoomByReservation($reservationId, $fromDate,$toDate)
    {
        $this->logger->debug("Starting Method: " . __METHOD__ );
        $responseArray = array();
        try{
            $blockedRoom = $this->em->getRepository(BlockedRooms::class)->findOneBy(array('linkedResaId' => $reservationId));
            if($blockedRoom != null){
                $toDateDateTime = new DateTime($toDate);
                $fromDateDateTime = new DateTime($fromDate);
                $blockedRoom->setFromDate($fromDateDateTime);
                $blockedRoom->setToDate($toDateDateTime);
                $blockedRoom->setUid(uniqid() . "@" . SERVER_NAME);

                $this->em->persist($blockedRoom);
                $this->em->flush($blockedRoom);

                $responseArray[] = array(
                    'result_code' => 0,
                    'result_message' => 'Successfully updated blocked room',
                    'block_id' => $blockedRoom->getId()
                );
                $this->logger->debug(print_r($responseArray, true));
            }else{
                $responseArray[] = array(
                    'result_message' => "No blocked room found for reservation",
                    'result_code'=> 1
                );
                $this->logger->debug("No blocked room found for reservation");
            }
        }catch(Exception $exception){
            $responseArray[] = array(
                'result_message' => $exception->getMessage(),
                'result_code'=> 1
            );
            $this->logger->error(print_r($responseArray, true));
        }
        $this->logger->debug("Ending Method before the return: " . __METHOD__ );
        return $responseArray;
    }


    public function getBlockedRoom($blockedRoomId)
    {
        $this->logger->debug("Starting Method: " . __METHOD__ );
        $responseArray = array();
        try{
            return $this->em->getRepository(BlockedRooms::class)->findOneBy(array('id' => $blockedRoomId));
        }catch(Exception $exception){
            $responseArray[] = array(
                'result_message' => $exception->getMessage(),
                'result_code'=> 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__ );
        return $responseArray;
    }


}