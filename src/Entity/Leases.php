<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Leases
 *
 * @ORM\Table(name="leases")
 * @ORM\Entity
 */
class Leases
{
    /**
     * @var int
     *
     * @ORM\Column(name="idleases", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idleases;

    /**
     * @var int|null
     *
     * @ORM\Column(name="tenant", type="integer", nullable=true)
     */
    private $tenant;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="start", type="datetime", nullable=true)
     */
    private $start;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="end", type="datetime", nullable=true)
     */
    private $end;

    /**
     * @var string|null
     *
     * @ORM\Column(name="contract", type="string", length=45, nullable=true)
     */
    private $contract;


}
