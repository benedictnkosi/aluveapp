<?php

namespace App\Helpers\FormatHtml;

use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Isset_;
use Psr\Log\LoggerInterface;

class RoomImagesHTML
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function formatHtml($roomImages): string
    {
        $html = '';
        foreach ($roomImages as $roomImage) {
            $html .= '<img class="mySlides" src="http://' . $_SERVER['SERVER_NAME'] . '/assets/images/rooms/' . $roomImage->getName() . '">';
        }

        if(count($roomImages)>0){
            $html .= '<button class="w3-button w3-black w3-display-left" onclick="plusDivs(-1)">&#10094;</button>
            <button class="w3-button w3-black w3-display-right" onclick="plusDivs(1)">&#10095;</button>';
        }

        return $html;
    }


    public function formatUpdateRoomHtml($roomImages): string
    {
        $html = '';
        //list of images

        if ($roomImages != null) {
            $html .= '<h5>Uploaded Images</h5>';
            foreach ($roomImages as $roomImage) {
                //check if default image
                $star = "star_gray.png";
                if(is_array($roomImage)){
                    $html .= '<h5>No images found</h5>';
                    return $html;
                }
                if (strcmp($roomImage->getStatus(), "default") === 0) {
                    $star = "star_yellow.png";
                }

                $html .= '<div class="img-wrap image-thumbnail" id="image-thumbnail-' . $roomImage->getId() . '">
                            <span class="close" data-image-id="' . $roomImage->getId() . '">&times;</span>
                            <span class="default_image_star_div" data-image-id="' . $roomImage->getId() . '"><img class="default_image_star" src="images/' . $star . '" data-image-id="' . $roomImage->getId() . '"></span>
                            <img class="" src="http://' . $_SERVER['SERVER_NAME'] . '/assets/images/rooms/' . $roomImage->getName() . '">
                        </div>';
            }
        } else {
            $html .= '<h5>No images found</h5>';
        }

        return $html;
    }
}