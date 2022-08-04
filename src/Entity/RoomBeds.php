<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomBeds
 *
 * @ORM\Table(name="room_beds", indexes={@ORM\Index(name="room_bed_room_bed_size_fk", columns={"bed"}), @ORM\Index(name="room_bed_room_fk", columns={"room"})})
 * @ORM\Entity
 */
class RoomBeds
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
     * @var RoomBedSize
     *
     * @ORM\ManyToOne(targetEntity="RoomBedSize")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="bed", referencedColumnName="id")
     * })
     */
    private $bed;

    /**
     * @var Rooms
     *
     * @ORM\ManyToOne(targetEntity="Rooms")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="room", referencedColumnName="id")
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
     * @return RoomBedSize
     */
    public function getBed(): RoomBedSize
    {
        return $this->bed;
    }

    /**
     * @param RoomBedSize $bed
     */
    public function setBed(RoomBedSize $bed): void
    {
        $this->bed = $bed;
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
