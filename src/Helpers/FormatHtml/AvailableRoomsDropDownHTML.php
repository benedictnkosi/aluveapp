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
        $htmlString = "";
        $roomsApi = new RoomApi($this->em, $this->logger);
        foreach ($availableRooms as $availableRoom) {
            $roomImages = $roomsApi->getRoomImages($availableRoom->getId());
            $roomDefaultImage = "noimage.png";
            foreach($roomImages as $roomImage){
                if(strcmp($roomImage->getStatus(),"default") ==0 ){
                    $roomDefaultImage = $roomImage->getName();
                }
            }

            $htmlString .='<option value="'.$availableRoom->getName().'"
                                                data-thumbnail="assets/images/rooms/thumb'.$roomDefaultImage. '" data-price="'.$availableRoom->getPrice().'" data-roomId="'.$availableRoom->getId().'">'.$availableRoom->getName().'
                                        </option>';
        }
        return $htmlString;
    }
}