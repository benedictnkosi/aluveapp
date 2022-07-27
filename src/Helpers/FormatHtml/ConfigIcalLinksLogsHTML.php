<?php

namespace App\Helpers\FormatHtml;

use App\Service\RoomApi;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

class ConfigIcalLinksLogsHTML
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function formatHtml($propertyUid,$roomsApi, $icalApi): string
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $html = '';
        $rooms = $roomsApi->getRoomsEntities($propertyUid);

        foreach($rooms as $room){
           $icalLinks =  $icalApi->getIcalLinks($room);
           if($icalLinks != null){
               $html .= '<h4>'.$room->getName().'</h4>';
           }
           foreach ($icalLinks as $icalLink){
               $html .= '<h5>'.$icalLink->getLink().'</h5>';
               $html .= '<pre>'.$icalLink->getLogs().'</pre>';
           }
            $html .= '<hr>';
        }

        return $html;
    }
}