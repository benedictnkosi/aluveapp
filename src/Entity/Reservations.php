<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Reservations
 *
 * @ORM\Table(name="reservations", indexes={@ORM\Index(name="reservations_ibfk_3", columns={"status"}), @ORM\Index(name="room_id", columns={"room_id"}), @ORM\Index(name="guest_id", columns={"guest_id"})})
 * @ORM\Entity
 */
class Reservations
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
     * @var DateTime
     *
     * @ORM\Column(name="check_in", type="date", nullable=false)
     */
    private $checkIn;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="check_out", type="date", nullable=false)
     */
    private $checkOut;

    /**
     * @var string
     *
     * @ORM\Column(name="additional_info", type="string", length=100, nullable=false)
     */
    private $additionalInfo;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="received_on", type="datetime", nullable=true)
     */
    private $receivedOn;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="updated_on", type="datetime", nullable=true)
     */
    private $updatedOn;

    /**
     * @var string
     *
     * @ORM\Column(name="uid", type="string", length=50, nullable=false)
     */
    private $uid;

    /**
     * @var string
     *
     * @ORM\Column(name="origin", type="string", length=100, nullable=false)
     */
    private $origin;

    /**
     * @var string
     *
     * @ORM\Column(name="origin_url", type="string", length=100, nullable=false)
     */
    private $originUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(name="check_in_status", type="string", length=45, nullable=true, options={"default"="not checked in"})
     */
    private $checkInStatus = 'not checked in';

    /**
     * @var int|null
     *
     * @ORM\Column(name="cleanliness_score", type="integer", nullable=true)
     */
    private $cleanlinessScore;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="checked_in_time", type="datetime", nullable=true)
     */
    private $checkedInTime;

    /**
     * @var string
     *
     * @ORM\Column(name="check_in_time", type="string", length=10, nullable=false, options={"default"="14:00"})
     */
    private $checkInTime = '14:00';

    /**
     * @var string
     *
     * @ORM\Column(name="check_out_time", type="string", length=10, nullable=false, options={"default"="10:00"})
     */
    private $checkOutTime = '10:00';

    /**
     * @var ReservationStatus
     *
     * @ORM\ManyToOne(targetEntity="ReservationStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status", referencedColumnName="id")
     * })
     */
    private $status;

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
     * @var Guest
     *
     * @ORM\ManyToOne(targetEntity="Guest")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="guest_id", referencedColumnName="id")
     * })
     */
    private $guest;

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
     * @return DateTime
     */
    public function getCheckIn(): DateTime
    {
        return $this->checkIn;
    }

    /**
     * @param DateTime $checkIn
     */
    public function setCheckIn(DateTime $checkIn): void
    {
        $this->checkIn = $checkIn;
    }

    /**
     * @return DateTime
     */
    public function getCheckOut(): DateTime
    {
        return $this->checkOut;
    }

    /**
     * @param DateTime $checkOut
     */
    public function setCheckOut(DateTime $checkOut): void
    {
        $this->checkOut = $checkOut;
    }

    /**
     * @return string
     */
    public function getAdditionalInfo(): string
    {
        return $this->additionalInfo;
    }

    /**
     * @param string $additionalInfo
     */
    public function setAdditionalInfo(string $additionalInfo): void
    {
        $this->additionalInfo = $additionalInfo;
    }

    /**
     * @return \DateTime|null
     */
    public function getReceivedOn(): ?\DateTime
    {
        return $this->receivedOn;
    }

    /**
     * @param \DateTime|null $receivedOn
     */
    public function setReceivedOn(?\DateTime $receivedOn): void
    {
        $this->receivedOn = $receivedOn;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedOn(): ?\DateTime
    {
        return $this->updatedOn;
    }

    /**
     * @param \DateTime|null $updatedOn
     */
    public function setUpdatedOn(?\DateTime $updatedOn): void
    {
        $this->updatedOn = $updatedOn;
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     */
    public function setUid(string $uid): void
    {
        $this->uid = $uid;
    }

    /**
     * @return string
     */
    public function getOrigin(): string
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     */
    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    /**
     * @return string
     */
    public function getOriginUrl(): string
    {
        return $this->originUrl;
    }

    /**
     * @param string $originUrl
     */
    public function setOriginUrl(string $originUrl): void
    {
        $this->originUrl = $originUrl;
    }

    /**
     * @return string|null
     */
    public function getCheckInStatus(): ?string
    {
        return $this->checkInStatus;
    }

    /**
     * @param string|null $checkInStatus
     */
    public function setCheckInStatus(?string $checkInStatus): void
    {
        $this->checkInStatus = $checkInStatus;
    }

    /**
     * @return int|null
     */
    public function getCleanlinessScore(): ?int
    {
        return $this->cleanlinessScore;
    }

    /**
     * @param int|null $cleanlinessScore
     */
    public function setCleanlinessScore(?int $cleanlinessScore): void
    {
        $this->cleanlinessScore = $cleanlinessScore;
    }

    /**
     * @return \DateTime|null
     */
    public function getCheckedInTime(): ?\DateTime
    {
        return $this->checkedInTime;
    }

    /**
     * @param \DateTime|null $checkedInTime
     */
    public function setCheckedInTime(?\DateTime $checkedInTime): void
    {
        $this->checkedInTime = $checkedInTime;
    }

    /**
     * @return string
     */
    public function getCheckInTime(): string
    {
        return $this->checkInTime;
    }

    /**
     * @param string $checkInTime
     */
    public function setCheckInTime(string $checkInTime): void
    {
        $this->checkInTime = $checkInTime;
    }

    /**
     * @return string
     */
    public function getCheckOutTime(): string
    {
        return $this->checkOutTime;
    }

    /**
     * @param string $checkOutTime
     */
    public function setCheckOutTime(string $checkOutTime): void
    {
        $this->checkOutTime = $checkOutTime;
    }

    /**
     * @return ReservationStatus
     */
    public function getStatus(): ReservationStatus
    {
        return $this->status;
    }

    /**
     * @param ReservationStatus $status
     */
    public function setStatus(ReservationStatus $status): void
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

    /**
     * @return Guest
     */
    public function getGuest(): Guest
    {
        return $this->guest;
    }

    /**
     * @param Guest $guest
     */
    public function setGuest(Guest $guest): void
    {
        $this->guest = $guest;
    }


}
