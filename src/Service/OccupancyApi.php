<?php

namespace App\Service;

use App\Entity\Property;
use App\Entity\ReservationStatus;
use App\Entity\Rooms;
use App\Entity\RoomStatus;
use App\Helpers\DatabaseHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class OccupancyApi
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        if(session_id() === ''){
            $logger->info("Session id is empty");
            session_start();
        }
    }

    public function getOccupancy($days)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();

        try {
            $confirmStatus = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));

            $sql = "SELECT room_id, rooms.name, (sum(
	DATEDIFF(IF(check_out<=DATE(NOW()), check_out, DATE(NOW())),
	IF(check_in<=DATE(NOW()) - INTERVAL " . $days . " DAY, DATE(NOW()) - INTERVAL " . $days . " DAY, check_in)))/" . $days . ")*100
         AS occupancy FROM reservations, rooms, property
WHERE rooms.Id =reservations.room_id
and rooms.property =property.id
and property.id = ".$_SESSION['PROPERTY_ID']."
and (DATE(check_in) >= DATE(NOW()) - INTERVAL " . $days . " DAY or DATE(check_out) >= DATE(NOW()) - INTERVAL " . $days . " DAY)
and DATE(check_in) < DATE(NOW())
and reservations.`status` = '".$confirmStatus->getId()."'
group by room_id
order by occupancy;";

            //echo $sql;
            $databaseHelper = new DatabaseHelper( $this->logger);
            $result = $databaseHelper->queryDatabase($sql);

            if (!$result) {
                $responseArray[] = array(
                    'result_message' => "No results found",
                    'result_code' => 1
                );
                $this->logger->debug(print_r($responseArray, true));
            } else {
                $numberOfUnits = 0;
                $sum = 0;

                while ($results = $result->fetch_assoc()) {
                    $numberOfUnits++;
                    $sum += round($results["occupancy"]);
                }

                $avg = $sum / $numberOfUnits;
                $responseArray[] = array(
                    'occupancy' => round($avg) . '%',
                    'result_code' => 0,
                    'result_message' => "success"
                );
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() .' - '. __METHOD__ . ':' . $ex->getLine() . ' ' .  $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("failed to get occupancy " . print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function getOccupancyPerRoom($days): string
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $htmlString = "";
        $responseArray = array();
        $roomIdsArray = array();
        try {
            $confirmStatus = $this->em->getRepository(ReservationStatus::class)->findOneBy(array('name' => 'confirmed'));

            $sql = "SELECT room_id , rooms.name, (sum(
	DATEDIFF(IF(check_out<=DATE(NOW()), check_out, DATE(NOW())),
	IF(check_in<=DATE(NOW()) - INTERVAL " . $days . " DAY, DATE(NOW()) - INTERVAL " . $days . " DAY, check_in)))/" . $days . ")*100
         AS occupancy FROM reservations, rooms, property
WHERE rooms.Id =reservations.room_id
and rooms.property =property.id
and property.id = ".$_SESSION['PROPERTY_ID']."
and (DATE(check_in) >= DATE(NOW()) - INTERVAL " . $days . " DAY or DATE(check_out) >= DATE(NOW()) - INTERVAL " . $days . " DAY)
and DATE(check_in) < DATE(NOW())
and reservations.`status` = '".$confirmStatus->getId()."'
group by room_id
order by occupancy;";
            $this->logger->debug($sql);

            $databaseHelper = new DatabaseHelper($this->logger);
            $result = $databaseHelper->queryDatabase($sql);

            if (!$result) {
                $responseArray[] = array(
                    'result_message' => "No results found",
                    'result_code' => 1
                );
                $this->logger->debug(print_r($responseArray,true));
            } else {

                while ($results = $result->fetch_assoc()) {
                    $htmlString .= '<h6>
								' . $results["name"] . ' <span> ' . round(intval($results["occupancy"])) . '% </span>
							</h6>
							<div class="progress">
								<div class="progress-bar progress-bar-striped active"
									style="width: ' . round(intval($results["occupancy"])) . '%"></div>
							</div>';
                    $roomIdsArray[] = $results["room_id"];
                }
            }

            //output all rooms without reservations for the period as zero
            $roomConfirmStatus = $this->em->getRepository(RoomStatus::class)->findOneBy(array('name' => 'live'));
            $property = $this->em->getRepository(Property::class)->findOneBy(array('id' => $_SESSION['PROPERTY_ID']));
            $rooms = $this->em->getRepository(Rooms::class)->findBy(array('property' => $property->getId(), 'status'=> $roomConfirmStatus->getId()));

            $this->logger->debug("room Ids array is " . print_r($roomIdsArray, true));

            foreach($rooms as $room){
                $this->logger->debug("checking room " . $room->getName());
                if (!in_array($room->getId(), $roomIdsArray)) {
                    $this->logger->debug("not in array");
                    $htmlString .= '<h6>
								' . $room->getName() . ' <span> 0% </span>
							</h6>
							<div class="progress">
								<div class="progress-bar progress-bar-striped active"
									style="width: 0%"></div>
							</div>';
                }else{
                    $this->logger->debug("in array");
                }
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() .' - '. __METHOD__ . ':' . $ex->getLine() . ' ' .  $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("failed to get occupancy " . print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $htmlString;
    }
}