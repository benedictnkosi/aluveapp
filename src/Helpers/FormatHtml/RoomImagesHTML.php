<?php

namespace App\Helpers\FormatHtml;

use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Isset_;
use Psr\Log\LoggerInterface;
require_once(__DIR__ . '/../app/application.php');

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
        $html = "";
        if ($roomImages != null) {

            $html .= '<div class="image-container">';
            foreach ($roomImages as $roomImage) {
                $html .= '<div class="slide">
                <img src="https:/'.SERVER_NAME.'/public/room/image/' . $roomImage->getName() . '">
            </div>';
            }

            $html .= '<a class="previous" onclick="moveSlides(-1)">
                <i class="fa fa-chevron-circle-left"></i>
            </a>
            <a class="next" onclick="moveSlides(1)">
                <i class="fa fa-chevron-circle-right"></i>
            </a>';

            $html .= '</div>
            <br>
            <div style="text-align:center">';
            $i = 0;
            foreach ($roomImages as $ignored) {
                $i++;
                $html .= ' <span class="footerdot"
            onclick="activeSlide('.$i.')">
        </span>';
            }
            $html .= '</div>';
        }else{
            $html .= '<div class="slide">
                <img src="https:/'.SERVER_NAME.'/public/room/image/room_noimage.jpg">
            </div>';
        }
        return $html;
    }


    public function formatUpdateRoomHtml($roomImages): string
    {
        $html = '';
        //list of images

        if ($roomImages != null) {
            $html .= '<h5>Uploaded Images (Click on image is to make it the default image)</h5>';
            foreach ($roomImages as $roomImage) {
                //check if default image

                if (is_array($roomImage)) {
                    $html .= '<h5>No images found </h5>';
                    return $html;
                }
                $imageNotDefaultClass = "not_default_image";
                if (strcmp($roomImage->getStatus(), "default") === 0) {
                    $imageNotDefaultClass = "";
                }

                $html .= '<div class="img-wrap image-thumbnail" id="image-thumbnail-' . $roomImage->getId() . '">
                            <span class="close" data-image-id="' . $roomImage->getId() . '">&times;</span>
                            <img data-image-id="' . $roomImage->getId() . '" class="room_images '.$imageNotDefaultClass.'" src="https:/'.SERVER_NAME.'/public/room/image/thumb' . $roomImage->getName() . '">
                        </div>';
            }
        } else {
            $html .= '<h5>No images found</h5>';
        }

        return $html;
    }
}