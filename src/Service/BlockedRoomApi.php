<?php

namespace App\Service;

use App\Entity\BlockedRooms;
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

    public function blockRoom($roomId, $date, $comments): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);


        $responseArray = array();
        try {
            $dateArray = explode(" - ", $date);

            //example date from request 06-28-2022%20-%2006-28-2022
            $fromDate = $dateArray[0];
            $toDate =  $dateArray[1];

            //change the format
            $fromDate = explode("-", $fromDate)[2] ."-" . explode("-", $fromDate)[0]."-" . explode("-", $fromDate)[1];
            $toDate = explode("-", $toDate)[2] ."-" . explode("-", $toDate)[0]."-" . explode("-", $toDate)[1];

            //get the room
            $roomApi = new RoomApi( $this->em, $this->logger);
            $room = $roomApi->getRoom($roomId);
            if($room == null){
                $responseArray[] = array(
                    'result_code' => 1,
                    'result_message' => "Room not found for id $roomId"
                );
                $this->logger->info("Ending Method before the return: " . __METHOD__);
                return $responseArray;
            }

            //check if the dates are the same, if same increment the to date by one day
            $toDateDateTime = new DateTime($toDate);
            $fromDateDateTime = new DateTime($fromDate);

            if($fromDateDateTime === $toDateDateTime){
                $toDateDateTime = new DateTime($toDate);
            }else{
                $toDateDateTime = new DateTime($toDate);
                $toDateDateTime = $toDateDateTime->modify('+1 day');
            }


            $blockRoom = new BlockedRooms();
            $blockRoom->setRoom($room);
            $blockRoom->setComment($comments);
            $blockRoom->setFromDate($fromDateDateTime);
            $blockRoom->setToDate($toDateDateTime);

            $this->em->persist($blockRoom);
            $this->em->flush($blockRoom);

            $responseArray[] = array(
                'result_code' => 0,
                'result_message' => 'Successfully blocked room'
            );
            $this->logger->info(print_r($responseArray, true));
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_code' => 1,
                'result_message' => $ex->getMessage()
            );
            $this->logger->info(print_r($responseArray, true));
        }


        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function getBlockedRooms($propertyUid, $roomId = 0): array
    {
        $this->logger->info("Starting Method: " . __METHOD__ );
        $responseArray = array();
        try{
            $datetime = new DateTime();
            $roomFilter = "";
            if($roomId != 0){
                $roomFilter = " and b.room = $roomId ";
            }
            $propertyApi = new PropertyApi($this->em, $this->logger);
            $propertyId =   $propertyApi->getPropertyIdByUid($propertyUid);
            $blockedRooms = $this->em
                ->createQuery("SELECT b FROM App\Entity\BlockedRooms b 
            WHERE b.toDate >= '".$datetime->format('Y-m-d')."' 
                    $roomFilter 
            order by b.fromDate asc ")
                ->getResult();

            $this->logger->info("Ending Method before the return: " . __METHOD__ );
            return $blockedRooms;
        }catch(Exception $exception){
            $responseArray[] = array(
                'result_message' => $exception->getMessage(),
                'result_code'=> 1
            );
            $this->logger->info(print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__ );
        return $responseArray;
    }


    public function deleteBlockedRoom($blockedRoomId): array
    {
        $this->logger->info("Starting Method: " . __METHOD__ );
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
            $this->logger->info(print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__ );
        return $responseArray;

    }

}