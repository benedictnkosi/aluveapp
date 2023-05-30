<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Units
 *
 * @ORM\Table(name="units", indexes={@ORM\Index(name="units_property_idx", columns={"property"})})
 * @ORM\Entity
 */
class Units
{
    /**
     * @var int
     *
     * @ORM\Column(name="idunits", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idunits;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="listed", type="boolean", nullable=true)
     */
    private $listed = '0';

    /**
     * @var Properties
     *
     * @ORM\ManyToOne(targetEntity="Properties")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="property", referencedColumnName="idProperties")
     * })
     */
    private $property;


}
