<?php

namespace App\Helpers\FormatHtml;

use App\Entity\RoomBeds;
use App\Service\RoomApi;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
require_once(__DIR__ . '/../../app/application.php');


class AvailableRoomsDropDownHTML
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function formatHtml($availableRooms): string
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $htmlString = "";
        $roomsApi = new RoomApi($this->em, $this->logger);
        $numberOfRooms = 0;
        if($availableRooms === null){
            $htmlString .='<option value="No Rooms Available for Selected Dates"
                                                data-thumbnail="'.PROTOCOL.'://'.SERVER_NAME.'/public/room/image/noroom.jpg" data-price="0" data-roomId="0"  data-sleeps="0">No Rooms Available
                                        </option>';
            return $htmlString;
        }
        foreach ($availableRooms as $availableRoom) {
            $roomImages = $roomsApi->getRoomImages($availableRoom->getId());
            $roomDefaultImage = "noimage.png";

            foreach ($roomImages as $roomImage) {
                if (strcmp($roomImage->getStatus(), "default") == 0) {
                    $roomDefaultImage = $roomImage->getName();
                }
            }

            $currentSelectedBeds = $this->em->getRepository(RoomBeds::class)->findBy(array('room' => $availableRoom->getId()));

            $beds = "";
            if($currentSelectedBeds !== null){
                foreach ($currentSelectedBeds as $currentSelectedBed){
                    $beds .= $currentSelectedBed->getBed()->getName() . ",";
                }
            }

            $beds = substr($beds,0,strlen($beds) - 1);

            $this->logger->debug("found beds string: " . $beds);
            $numberOfRooms++;
            $htmlString .= '<option value="' . $availableRoom->getName() . '"
                                                data-thumbnail="'.PROTOCOL.'://'.SERVER_NAME.'/public/room/image/thumb' . $roomDefaultImage . '" data-sleeps="' . $availableRoom->getSleeps() . '" data-price="' . $availableRoom->getPrice() . '" data-roomId="' . $availableRoom->getId() . '" data-beds="' . $beds . '">' . $availableRoom->getName() . '
                                        </option>';

        }
        $this->logger->debug("ending Method: " . __METHOD__);
        return $htmlString;
    }
}