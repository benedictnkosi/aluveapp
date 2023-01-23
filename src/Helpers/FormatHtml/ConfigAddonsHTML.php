<?php

namespace App\Helpers\FormatHtml;

use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Isset_;
use Psr\Log\LoggerInterface;

class ConfigAddonsHTML
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function formatHtml($addOns): string
    {
        $html = '';
        if ($addOns != null) {
            foreach ($addOns as $addOn) {
                $html .= '<div class="addon_row">
                        <div class="addon-left-div">
                            <label>Add-ons Name</label>
                            <input type="text" class="addon_field" data-addon-id="'.$addOn->getId().'" data-addon-field="name" value="'.$addOn->getName().'"
                                   required/>
                                   <div class="ClickableButton remove_addon_button" data-addon-id="'.$addOn->getId().'" >Remove</div>
                                   
                        </div>
                        <div class="addon-right-div">
                            <label>Price</label>
                            <input type="text" class="addon_field" data-addon-id="'.$addOn->getId().'" data-addon-field="price" value="'.$addOn->getPrice().'"
                                   required/>
                                   
                        </div>
                        
                        <div class="addon-right-div">
                            <label>Quantity</label>
                            <input type="text" class="addon_field" data-addon-id="'.$addOn->getId().'" data-addon-field="quantity" value="'.$addOn->getQuantity().'"
                                   required/>
                                   
                        </div>
                    </div>';
            }
        } else {
            $html .= '<h5>No add-ons found</h5>';
        }

        return $html;
    }
}