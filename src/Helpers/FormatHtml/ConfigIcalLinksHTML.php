<?php

namespace App\Helpers\FormatHtml;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

class ConfigIcalLinksHTML
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function formatHtml($icalLinks): string
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $html = '';
        if ($icalLinks != null) {
            foreach ($icalLinks as $icalLink) {
                $html .= '<div class="addon_row">
                        <label>Channel Link</label>
                            <input type="text" class="addon_field" data-link-id="'.$icalLink->getId().'" data-link-field="link" value="'.$icalLink->getLink().'"
                                   required/>
                                   <div class="ClickableButton remove_link_button" data-link-id="'.$icalLink->getId().'" >Remove</div>
                    </div>';
            }
        } else {
            $html .= '<h5>No Links found</h5>';
        }

        return $html;
    }
}