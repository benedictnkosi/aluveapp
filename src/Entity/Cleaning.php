<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cleaning
 *
 * @ORM\Table(name="cleaning", indexes={@ORM\Index(name="cleaning_reservation", columns={"reservation_id"}), @ORM\Index(name="cleaning_cleaner", columns={"cleaner"})})
 * @ORM\Entity
 */
class Cleaning
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $date = 'CURRENT_TIMESTAMP';

    /**
     * @var Reservations
     *
     * @ORM\ManyToOne(targetEntity="Reservations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="reservation_id", referencedColumnName="id")
     * })
     */
    private $reservation;

    /**
     * @var Employee
     *
     * @ORM\ManyToOne(targetEntity="Employee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cleaner", referencedColumnName="id")
     * })
     */
    private $cleaner;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime|string
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime|string $date): void
    {
        $this->date = $date;
    }

    /**
     * @return Reservations
     */
    public function getReservation(): Reservations
    {
        return $this->reservation;
    }

    /**
     * @param Reservations $reservation
     */
    public function setReservation(Reservations $reservation): void
    {
        $this->reservation = $reservation;
    }

    /**
     * @return Employee
     */
    public function getCleaner(): Employee
    {
        return $this->cleaner;
    }

    /**
     * @param Employee $cleaner
     */
    public function setCleaner(Employee $cleaner): void
    {
        $this->cleaner = $cleaner;
    }


}
