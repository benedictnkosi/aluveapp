<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 *
 * @ORM\Table(name="config")
 * @ORM\Entity
 */
class Config
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
     * @ORM\Column(name="airbnb_email", type="string", length=100, nullable=false)
     */
    private $airbnbEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="airbnb_email_password", type="string", length=100, nullable=false)
     */
    private $airbnbEmailPassword;

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
    public function getAirbnbEmail(): string
    {
        return $this->airbnbEmail;
    }

    /**
     * @param string $airbnbEmail
     */
    public function setAirbnbEmail(string $airbnbEmail): void
    {
        $this->airbnbEmail = $airbnbEmail;
    }

    /**
     * @return string
     */
    public function getAirbnbEmailPassword(): string
    {
        return $this->airbnbEmailPassword;
    }

    /**
     * @param string $airbnbEmailPassword
     */
    public function setAirbnbEmailPassword(string $airbnbEmailPassword): void
    {
        $this->airbnbEmailPassword = $airbnbEmailPassword;
    }


}
