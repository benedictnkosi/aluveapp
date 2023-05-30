<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Contractors
 *
 * @ORM\Table(name="contractors")
 * @ORM\Entity
 */
class Contractors
{
    /**
     * @var int
     *
     * @ORM\Column(name="idcontractors", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idcontractors;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone_number", type="string", length=45, nullable=true)
     */
    private $phoneNumber;


}
