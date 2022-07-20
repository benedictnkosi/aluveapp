<?php

namespace App\Helpers\FormatHtml;

use App\Service\RoomApi;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

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
        $this->logger->info("Starting Method: " . __METHOD__);
        $htmlString = "";
        $roomsApi = new RoomApi($this->em, $this->logger);
        $numberOfRooms = 0;
        foreach ($availableRooms as $availableRoom) {
            $roomImages = $roomsApi->getRoomImages($availableRoom->getId());
            $roomDefaultImage = "noimage.png";

            foreach ($roomImages as $roomImage) {
                if (strcmp($roomImage->getStatus(), "default") == 0) {
                    $roomDefaultImage = $roomImage->getName();
                }
            }
            $numberOfRooms++;
            $htmlString .= '<option value="' . $availableRoom->getName() . '"
                                                data-thumbnail="assets/images/rooms/thumb' . $roomDefaultImage . '" data-sleeps="' . $availableRoom->getSleeps() . '" data-price="' . $availableRoom->getPrice() . '" data-roomId="' . $availableRoom->getId() . '">' . $availableRoom->getName() . '
                                        </option>';

        }

        if($numberOfRooms === 0){
            $htmlString .='<option value="No Rooms Available for Selected Dates"
                                                data-thumbnail="assets/images/rooms/noroom.jpg" data-price="0" data-roomId="0"  data-sleeps="0">No Rooms Available
                                        </option>';
        }
        $this->logger->info("ending Method: " . __METHOD__);
        return $htmlString;
    }
}