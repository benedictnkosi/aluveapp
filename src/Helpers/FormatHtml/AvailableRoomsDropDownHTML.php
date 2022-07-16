<?php

namespace App\Helpers\FormatHtml;

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

        foreach ($availableRooms as $availableRoom) {
            $htmlString .='<option value="'.$availableRoom->getName().'"
                                                data-thumbnail="assets/images/room_thumbnail/'.$availableRoom->getId().'.jpg" data-price="'.$availableRoom->getPrice().'" data-roomId="'.$availableRoom->getId().'">'.$availableRoom->getName().'
                                        </option>';
        }

        return $htmlString;
    }
}