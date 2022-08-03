<?php

namespace App\Helpers\FormatHtml;

use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Isset_;
use Psr\Log\LoggerInterface;

class ConfigEmployeesHTML
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function formatHtml($employees): string
    {
        $html = '';
        if ($employees != null) {
            foreach ($employees as $employee) {
                $html .= '<div class="employee_row">
                        
                            <input type="text" class="employee_field" data-employee-id="'.$employee->getId().'" value="'.$employee->getName().'"
                                   required/>
                                   <div class="ClickableButton remove_employee_button" data-employee-id="'.$employee->getId().'" >Remove</div>
                        
                    </div>';
            }
        } else {
            $html .= '<h5>No employees found</h5>';
        }

        return $html;
    }
}