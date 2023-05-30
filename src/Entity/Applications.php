<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Applications
 *
 * @ORM\Table(name="applications", indexes={@ORM\Index(name="application_unit", columns={"unit"}), @ORM\Index(name="application_status_idx", columns={"status"})})
 * @ORM\Entity
 */
class Applications
{
    /**
     * @var int
     *
     * @ORM\Column(name="idapplications", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idapplications;

    /**
     * @var string|null
     *
     * @ORM\Column(name="applicant_name", type="string", length=45, nullable=true)
     */
    private $applicantName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="applicant_phone", type="string", length=45, nullable=true)
     */
    private $applicantPhone;

    /**
     * @var string|null
     *
     * @ORM\Column(name="applicant_email", type="string", length=45, nullable=true)
     */
    private $applicantEmail;

    /**
     * @var int|null
     *
     * @ORM\Column(name="applicant_salary", type="integer", nullable=true)
     */
    private $applicantSalary;

    /**
     * @var string|null
     *
     * @ORM\Column(name="reference_name", type="string", length=45, nullable=true)
     */
    private $referenceName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="reference_phone", type="string", length=45, nullable=true)
     */
    private $referencePhone;

    /**
     * @var string|null
     *
     * @ORM\Column(name="bank_statement", type="string", length=45, nullable=true)
     */
    private $bankStatement;

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
     * @var ApplicationStatuses
     *
     * @ORM\ManyToOne(targetEntity="ApplicationStatuses")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status", referencedColumnName="idapplication_statuses")
     * })
     */
    private $status;


}
