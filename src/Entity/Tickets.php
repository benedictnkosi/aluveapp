<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tickets
 *
 * @ORM\Table(name="tickets", indexes={@ORM\Index(name="ticket_property", columns={"property"})})
 * @ORM\Entity
 */
class Tickets
{
    /**
     * @var int
     *
     * @ORM\Column(name="idTicket", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idticket;

    /**
     * @var string|null
     *
     * @ORM\Column(name="summary", type="string", length=45, nullable=true)
     */
    private $summary;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=0, nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="property_contractor", type="string", length=45, nullable=true)
     */
    private $propertyContractor;

    /**
     * @var Property
     *
     * @ORM\ManyToOne(targetEntity="Property")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="property", referencedColumnName="id")
     * })
     */
    private $property;


}
