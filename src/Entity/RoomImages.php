<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomImages
 *
 * @ORM\Table(name="room_images", indexes={@ORM\Index(name="room_id_room", columns={"room_id"})})
 * @ORM\Entity
 */
class RoomImages
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
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=10, nullable=false, options={"default"="active"})
     */
    private $status = 'active';

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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
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
