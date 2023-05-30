<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tenant
 *
 * @ORM\Table(name="tenant", indexes={@ORM\Index(name="tenant_unit_idx", columns={"unit"}), @ORM\Index(name="debit_order_idx", columns={"debit_order"})})
 * @ORM\Entity
 */
class Tenant
{
    /**
     * @var int
     *
     * @ORM\Column(name="idtenant", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idtenant;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone", type="string", length=45, nullable=true)
     */
    private $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", length=45, nullable=true)
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="quickbooks_ref", type="string", length=45, nullable=true)
     */
    private $quickbooksRef;

    /**
     * @var Units
     *
     * @ORM\ManyToOne(targetEntity="Units")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="unit", referencedColumnName="idunits")
     * })
     */
    private $unit;

    /**
     * @var DebitOrder
     *
     * @ORM\ManyToOne(targetEntity="DebitOrder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="debit_order", referencedColumnName="iddebit_order")
     * })
     */
    private $debitOrder;


}
