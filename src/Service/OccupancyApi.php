<?php

namespace App\Service;

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

    public function getOccupancy($days, $propertyUid)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();

        try {
            $sql = "SELECT room_id, rooms.name, (sum(
	DATEDIFF(IF(check_out<=DATE(NOW()), check_out, DATE(NOW())),
	IF(check_in<=DATE(NOW()) - INTERVAL " . $days . " DAY, DATE(NOW()) - INTERVAL " . $days . " DAY, check_in)))/" . $days . ")*100
         AS occupancy FROM reservations, rooms, property
WHERE rooms.Id =reservations.room_id
and rooms.property =property.id
and property.uid = $propertyUid
and (DATE(check_in) >= DATE(NOW()) - INTERVAL " . $days . " DAY or DATE(check_out) >= DATE(NOW()) - INTERVAL " . $days . " DAY)
and DATE(check_in) < DATE(NOW())
and reservations.`status` = 'confirmed'
group by room_id
order by occupancy;";

            //echo $sql;
            $databaseHelper = new DatabaseHelper( $this->logger);
            $result = $databaseHelper->queryDatabase($sql);

            if (!$result) {
                $responseArray[] = array(
                    'result_message' => "failed to run query on database",
                    'result_code' => 1
                );
                $this->logger->info(print_r($responseArray, true));
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
                    'result_description' => "success"
                );
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info("failed to get occupancy " . print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function getOccupancyPerRoom($days, $propertyUid): string
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $htmlString = "";
        $responseArray = array();
        try {
            $sql = "SELECT room_id, rooms.name, (sum(
	DATEDIFF(IF(check_out<=DATE(NOW()), check_out, DATE(NOW())),
	IF(check_in<=DATE(NOW()) - INTERVAL " . $days . " DAY, DATE(NOW()) - INTERVAL " . $days . " DAY, check_in)))/" . $days . ")*100
         AS occupancy FROM reservations, rooms, property
WHERE rooms.Id =reservations.room_id
and rooms.property =property.id
and property.uid = $propertyUid 
and (DATE(check_in) >= DATE(NOW()) - INTERVAL " . $days . " DAY or DATE(check_out) >= DATE(NOW()) - INTERVAL " . $days . " DAY)
and DATE(check_in) < DATE(NOW())
and reservations.`status` = 'confirmed'
group by room_id
order by occupancy;";

            //echo $sql;
            $databaseHelper = new DatabaseHelper($this->logger);
            $result = $databaseHelper->queryDatabase($sql);

            if (!$result) {
                $responseArray[] = array(
                    'result_message' => "failed to run query on database",
                    'result_code' => 1
                );
                $this->logger->info(print_r($responseArray,true));
            } else {

                while ($results = $result->fetch_assoc()) {

                    $htmlString .= '<h6>
								' . $results["name"] . ' <span> ' . round(intval($results["occupancy"])) . '% </span>
							</h6>
							<div class="progress">
								<div class="progress-bar progress-bar-striped active"
									style="width: ' . round(intval($results["occupancy"])) . '%"></div>
							</div>';

                }
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
            $this->logger->info("failed to get occupancy " . print_r($responseArray, true));
        }

        $this->logger->info("Ending Method before the return: " . __METHOD__);
        return $htmlString;
    }
}