<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BlockedRooms
 *
 * @ORM\Table(name="blocked_rooms", indexes={@ORM\Index(name="room_id", columns={"room_id"})})
 * @ORM\Entity
 */
class BlockedRooms
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
     * @var \DateTime|null
     *
     * @ORM\Column(name="from_date", type="date", nullable=true)
     */
    private $fromDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="to_date", type="date", nullable=true)
     */
    private $toDate;

    /**
     * @var int|null
     *
     * @ORM\Column(name="linked_resa_id", type="integer", nullable=true)
     */
    private $linkedResaId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="comment", type="string", length=45, nullable=true)
     */
    private $comment;

    /**
     * @var Rooms
     *
     * @ORM\ManyToOne(targetEntity="Rooms")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     * })
     */
    private $room;

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
     * @return \DateTime|null
     */
    public function getFromDate(): ?\DateTime
    {
        return $this->fromDate;
    }

    /**
     * @param \DateTime|null $fromDate
     */
    public function setFromDate(?\DateTime $fromDate): void
    {
        $this->fromDate = $fromDate;
    }

    /**
     * @return \DateTime|null
     */
    public function getToDate(): ?\DateTime
    {
        return $this->toDate;
    }

    /**
     * @param \DateTime|null $toDate
     */
    public function setToDate(?\DateTime $toDate): void
    {
        $this->toDate = $toDate;
    }

    /**
     * @return int|null
     */
    public function getLinkedResaId(): ?int
    {
        return $this->linkedResaId;
    }

    /**
     * @param int|null $linkedResaId
     */
    public function setLinkedResaId(?int $linkedResaId): void
    {
        $this->linkedResaId = $linkedResaId;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     */
    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @return Rooms
     */
    public function getRoom(): Rooms
    {
        return $this->room;
    }

    /**
     * @param Rooms $room
     */
    public function setRoom(Rooms $room): void
    {
        $this->room = $room;
    }


}
