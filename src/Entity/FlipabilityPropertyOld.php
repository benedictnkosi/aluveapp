<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FlipabilityPropertyOld
 *
 * @ORM\Table(name="flipability_property_old")
 * @ORM\Entity
 */
class FlipabilityPropertyOld
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(name="bedrooms", type="integer", nullable=true)
     */
    private $bedrooms;

    /**
     * @var string|null
     *
     * @ORM\Column(name="url", type="text", length=65535, nullable=true)
     */
    private $url;

    /**
     * @var int|null
     *
     * @ORM\Column(name="price", type="integer", nullable=true)
     */
    private $price;

    /**
     * @var string|null
     *
     * @ORM\Column(name="location", type="text", length=65535, nullable=true)
     */
    private $location;

    /**
     * @var float|null
     *
     * @ORM\Column(name="bathrooms", type="float", precision=10, scale=0, nullable=true)
     */
    private $bathrooms;

    /**
     * @var float|null
     *
     * @ORM\Column(name="garage", type="float", precision=10, scale=0, nullable=true)
     */
    private $garage;

    /**
     * @var int|null
     *
     * @ORM\Column(name="erf", type="bigint", nullable=true)
     */
    private $erf;

    /**
     * @var string|null
     *
     * @ORM\Column(name="type", type="text", length=65535, nullable=true)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $timestamp = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=20, nullable=false, options={"default"="new"})
     */
    private $state = 'new';


}
