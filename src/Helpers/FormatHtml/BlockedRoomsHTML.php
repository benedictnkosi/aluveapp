<?php

namespace App\Helpers\FormatHtml;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

class BlockedRoomsHTML
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function formatHtml($blockedRooms): string
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $htmlString = "";

        //if no reservations found
        if (count($blockedRooms) < 1) {
            return '<div class="res-details">
						<h4 class="guest-name">No blocked rooms found</h4>
					</div>';
        }

        foreach ($blockedRooms as $blockedRoom) {
            //heading
            $htmlString .= '<div class="res-details">';
            //room name
            $htmlString .= '<h4 class="guest-name">' . $blockedRoom->getRoom()->getName() . ' - ' . $blockedRoom->getId() . '</h4>';
            //dates
            $htmlString .= '<p name="res-dates">' . $blockedRoom->getFromDate()->format("d M") .  ' - ' . $blockedRoom->getToDate()->format("d M") . '</p>';
            //comments
            $htmlString .= '<p name="res-dates">' . $blockedRoom->getComment(). '</p>';
            //bottom right action buttons
            $htmlString .= '<p class="far-right"><span class="glyphicon glyphicon-trash deleteBlockRoom clickable" aria-hidden="true" id="delete_blocked_' . $blockedRoom->getId() . '"></span></p>';
            //close tags
            $htmlString .= '<div class="clearfix"></div></div>';
        }

        return $htmlString;
    }
}