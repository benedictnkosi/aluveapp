<?php

namespace App\Helpers\FormatHtml;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class RoomsPageHTML
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function formatHtml($rooms, $roomApi): string
    {
        $html = "";
        foreach ($rooms as $room){
            $roomImages = $roomApi->getRoomImages($room->getId());
            $roomDefaultImage = "noimage.png";
            foreach($roomImages as $roomImage){
                if(strcmp($roomImage->getStatus(),"default") ==0 ){
                    $roomDefaultImage = $roomImage->getName();
                }
            }

            $roomId = $room->getId();
            $roomName = $room->getName();
            $roomPrice = $room->getPrice();
            $sleeps = $room->getSleeps();
            $size = $room->getSize();
            $bed = $room->getBed()->getName();
            $stairs = $room->getStairs();
            $description = $room->getDescription();

            $html.='<div class="maghny-gd-1 col-lg-4 col-md-6">
                <div class="maghny-grid">
                    <figure class="effect-lily">
                        <img class="img-fluid" src="assets/images/rooms/'.$roomDefaultImage. '" alt="">
                        <figcaption>
                            <div>
                                <h4 class="top-text">
                                    <ul>
                                        <li> <span class="fa fa-star"></span></li>
                                        <li> <span class="fa fa-star"></span></li>
                                        <li> <span class="fa fa-star"></span></li>
                                        <li> <span class="fa fa-star"></span></li>
                                        <li> <span class="fa fa-star-o"></span></li>
                                    </ul>
                                </h4>
                                <p>Book for R'.$roomPrice.' </p>
                            </div>
                        </figcaption>
                    </figure>
                    <div class="room-info">
                        <h3 class="room-title"><a href="/room.html?id='.$roomId.'">'.$roomName.'</a></h3>
                        <ul class="mb-3">
                            <li><span class="fa fa-users"></span> '.$sleeps.' Guests</li>
                            <li><span class="fa fa-bed"></span> '.$size.' m2</li>
                        </ul>
                        <p><pre>'. substr($description, 0, 100).'...</pre></p>
                        <a href="/booking.html" class="btn mt-sm-4 mt-3">Book Now</a>
                        <div class="room-info-bottom">
                            <ul class="room-amenities">
                                <li><a href="javascript:void(0)"><span class="fa fa-bed" title="Bed"></span></a></li>
                                <li><a href="javascript:void(0)"><span class="fa fa-television" title="Television"></span></a></li>
                                <li><a href="javascript:void(0)"><span class="fa fa-bath" title="Private Bathroom"></span></a></li>
                                <li><a href="javascript:void(0)"><span class="fa fa-wifi" title="Uncapped Wifi"></span></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>';
        }

        return $html;
    }
}