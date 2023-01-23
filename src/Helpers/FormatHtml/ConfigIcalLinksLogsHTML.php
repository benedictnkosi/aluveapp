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

    public function formatHtml($roomsApi, $icalApi): string
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $html = '';
        $rooms = $roomsApi->getRoomsEntities();

        foreach ($rooms as $room) {
            //output last export date for Airbnb and Booking.com

            $icalLinks = $icalApi->getIcalLinks($room);
            if ($icalLinks != null) {
                $html .= '<h4>' . $room->getName() . '</h4>';
            }

            $html .= '<h5>Airbnb Last Export : ' . $room->getAirbnbLastExport()->format("Y-m-d H:i") . '</h5>';
            $html .= '<h5>Booking.com Last Export : ' . $room->getBdcLastExport()->format("Y-m-d H:i") . '</h5>';

            foreach ($icalLinks as $icalLink) {
                $html .= '<h5>' . $icalLink->getLink() . '</h5>';
                $html .= '<pre>' . $icalLink->getLogs() . '</pre>';
            }
            $html .= '<hr>';
        }

        return $html;
    }
}