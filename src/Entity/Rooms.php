<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rooms
 *
 * @ORM\Table(name="rooms", indexes={@ORM\Index(name="fk_room_property", columns={"property"}), @ORM\Index(name="rooms_ibfk_2", columns={"status"}), @ORM\Index(name="rooms_ibfk_1", columns={"bed"})})
 * @ORM\Entity
 */
class Rooms
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
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=0, nullable=true)
     */
    private $price;

    /**
     * @var int|null
     *
     * @ORM\Column(name="sleeps", type="integer", nullable=true)
     */
    private $sleeps;

    /**
     * @var int|null
     *
     * @ORM\Column(name="linked_room", type="integer", nullable=true)
     */
    private $linkedRoom;

    /**
     * @var int|null
     *
     * @ORM\Column(name="size", type="integer", nullable=true)
     */
    private $size;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="stairs", type="boolean", nullable=true)
     */
    private $stairs;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var RoomStatus
     *
     * @ORM\ManyToOne(targetEntity="RoomStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * @var Property
     *
     * @ORM\ManyToOne(targetEntity="Property")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="property", referencedColumnName="id")
     * })
     */
    private $property;

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
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getPrice(): ?string
    {
        return $this->price;
    }

    /**
     * @param string|null $price
     */
    public function setPrice(?string $price): void
    {
        $this->price = $price;
    }

    /**
     * @return int|null
     */
    public function getSleeps(): ?int
    {
        return $this->sleeps;
    }

    /**
     * @param int|null $sleeps
     */
    public function setSleeps(?int $sleeps): void
    {
        $this->sleeps = $sleeps;
    }

    /**
     * @return int|null
     */
    public function getLinkedRoom(): ?int
    {
        return $this->linkedRoom;
    }

    /**
     * @param int|null $linkedRoom
     */
    public function setLinkedRoom(?int $linkedRoom): void
    {
        $this->linkedRoom = $linkedRoom;
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @param int|null $size
     */
    public function setSize(?int $size): void
    {
        $this->size = $size;
    }

    /**
     * @return bool|null
     */
    public function getStairs(): ?bool
    {
        return $this->stairs;
    }

    /**
     * @param bool|null $stairs
     */
    public function setStairs(?bool $stairs): void
    {
        $this->stairs = $stairs;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return RoomStatus
     */
    public function getStatus(): RoomStatus
    {
        return $this->status;
    }

    /**
     * @param RoomStatus $status
     */
    public function setStatus(RoomStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * @return Property
     */
    public function getProperty(): Property
    {
        return $this->property;
    }

    /**
     * @param Property $property
     */
    public function setProperty(Property $property): void
    {
        $this->property = $property;
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


}
