<?php

namespace App\Helpers\FormatHtml;

use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Isset_;
use Psr\Log\LoggerInterface;

class ConfigGuestsHTML
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function formatHtml($guests): string
    {
        $html = '';
        if ($guests != null) {
            foreach ($guests as $guest) {
                $rewards = '';
                if($guest->isRewards()){
                    $rewards = 'checked';
                }
                $html .= '<div class="addon_row">
                        <div class="addon-left-div">
                            <label>Name</label>
                            <input type="text" class="guest_field" data-guest-id="'.$guest->getId().'" data-guest-field="name" value="'.$guest->getName().'"
                                   required/>
                                   <div class="ClickableButton remove_addon_button" data-addon-id="'.$guest->getId().'" >Remove</div>
                                   
                        </div>
                        <div class="addon-right-div">
                            <label>Phone Number</label>
                            <input type="text" class="guest_field" data-guest-id="'.$guest->getId().'" data-guest-field="phoneNumber" value="'.$guest->getPhoneNumber().'"
                                   required/>
                                   
                        </div>
                        
                        <div class="addon-right-div">
                            <label>Rewards</label>
                            <input type="checkbox" id="rewards_'.$guest->getId().'"  class="guest_field" data-guest-id="'.$guest->getId().'" data-guest-field="rewards" name="rewards" value="Rewards" '.$rewards.'>
                                   
                        </div>
                    </div>';
            }
        } else {
            $html .= '<h5>No Guests found</h5>';
        }

        return $html;
    }
}