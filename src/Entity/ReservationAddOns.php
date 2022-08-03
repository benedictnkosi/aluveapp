<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReservationAddOns
 *
 * @ORM\Table(name="reservation_add_ons", indexes={@ORM\Index(name="reservation_add_on_add_on", columns={"add_on_id"}), @ORM\Index(name="reservation_add_on_reservation", columns={"reservation_id"})})
 * @ORM\Entity
 */
class ReservationAddOns
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
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     */
    private $quantity;

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
     * @var AddOns
     *
     * @ORM\ManyToOne(targetEntity="AddOns")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="add_on_id", referencedColumnName="id")
     * })
     */
    private $addOn;

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
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
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
     * @return AddOns
     */
    public function getAddOn(): AddOns
    {
        return $this->addOn;
    }

    /**
     * @param AddOns $addOn
     */
    public function setAddOn(AddOns $addOn): void
    {
        $this->addOn = $addOn;
    }


}
