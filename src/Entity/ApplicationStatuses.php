<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ApplicationStatuses
 *
 * @ORM\Table(name="application_statuses")
 * @ORM\Entity
 */
class ApplicationStatuses
{
    /**
     * @var int
     *
     * @ORM\Column(name="idapplication_statuses", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idapplicationStatuses;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;


}
