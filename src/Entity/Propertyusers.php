<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Propertyusers
 *
 * @ORM\Table(name="propertyusers", indexes={@ORM\Index(name="property_idx", columns={"property"}), @ORM\Index(name="users_idx", columns={"user"})})
 * @ORM\Entity
 */
class Propertyusers
{
    /**
     * @var int
     *
     * @ORM\Column(name="idPropertyUsers", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idpropertyusers;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id")
     * })
     */
    private $user;

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
