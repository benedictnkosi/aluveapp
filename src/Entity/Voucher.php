<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Voucher
 *
 * @ORM\Table(name="voucher")
 * @ORM\Entity
 */
class Voucher
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
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=6, nullable=false)
     */
    private $code;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $creationDate = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20, nullable=false, options={"default"="new"})
     */
    private $status = 'new';

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=20, nullable=false)
     */
    private $phoneNumber;


}
