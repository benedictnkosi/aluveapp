<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TicketsContractor
 *
 * @ORM\Table(name="tickets_contractor", indexes={@ORM\Index(name="tickets_contractor_ticket_idx", columns={"ticket"}), @ORM\Index(name="tickets_contractor_contractor_idx", columns={"contractor"})})
 * @ORM\Entity
 */
class TicketsContractor
{
    /**
     * @var int
     *
     * @ORM\Column(name="idtickets_contractor", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idticketsContractor;

    /**
     * @var Tickets
     *
     * @ORM\ManyToOne(targetEntity="Tickets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ticket", referencedColumnName="idTicket")
     * })
     */
    private $ticket;

    /**
     * @var Contractors
     *
     * @ORM\ManyToOne(targetEntity="Contractors")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contractor", referencedColumnName="idcontractors")
     * })
     */
    private $contractor;


}
