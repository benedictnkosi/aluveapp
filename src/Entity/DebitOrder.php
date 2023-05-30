<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DebitOrder
 *
 * @ORM\Table(name="debit_order")
 * @ORM\Entity
 */
class DebitOrder
{
    /**
     * @var int
     *
     * @ORM\Column(name="iddebit_order", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $iddebitOrder;

    /**
     * @var string|null
     *
     * @ORM\Column(name="bank_name", type="string", length=45, nullable=true)
     */
    private $bankName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="account_number", type="string", length=20, nullable=true)
     */
    private $accountNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="account_holder", type="string", length=100, nullable=true)
     */
    private $accountHolder;


}
