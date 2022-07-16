<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ScheduleMessages
 *
 * @ORM\Table(name="schedule_messages", indexes={@ORM\Index(name="schedule_message_times", columns={"message_schedule"}), @ORM\Index(name="schedule_message_template", columns={"message_template"}), @ORM\Index(name="FK_schedule_messages_room", columns={"room"})})
 * @ORM\Entity
 */
class ScheduleMessages
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
     * @var ScheduleTimes
     *
     * @ORM\ManyToOne(targetEntity="ScheduleTimes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="message_schedule", referencedColumnName="id")
     * })
     */
    private $messageSchedule;

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
     * @var MessageTemplate
     *
     * @ORM\ManyToOne(targetEntity="MessageTemplate")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="message_template", referencedColumnName="id")
     * })
     */
    private $messageTemplate;

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
     * @return ScheduleTimes
     */
    public function getMessageSchedule(): ScheduleTimes
    {
        return $this->messageSchedule;
    }

    /**
     * @param ScheduleTimes $messageSchedule
     */
    public function setMessageSchedule(ScheduleTimes $messageSchedule): void
    {
        $this->messageSchedule = $messageSchedule;
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
     * @return MessageTemplate
     */
    public function getMessageTemplate(): MessageTemplate
    {
        return $this->messageTemplate;
    }

    /**
     * @param MessageTemplate $messageTemplate
     */
    public function setMessageTemplate(MessageTemplate $messageTemplate): void
    {
        $this->messageTemplate = $messageTemplate;
    }


}
