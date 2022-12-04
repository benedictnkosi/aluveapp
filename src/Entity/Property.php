<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Property
 *
 * @ORM\Table(name="property")
 * @ORM\Entity
 */
class Property
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
     * @ORM\Column(name="address", type="string", length=100, nullable=false)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=20, nullable=false)
     */
    private $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="secret", type="string", length=10, nullable=false)
     */
    private $secret;

    /**
     * @var string
     *
     * @ORM\Column(name="email_address", type="string", length=100, nullable=false)
     */
    private $emailAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="admin_email", type="string", length=100, nullable=false)
     */
    private $adminEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="server_name", type="string", length=100, nullable=false)
     */
    private $serverName;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_name", type="string", length=100, nullable=false)
     */
    private $bankName;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account_type", type="string", length=100, nullable=false)
     */
    private $bankAccountType;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account_number", type="string", length=100, nullable=false)
     */
    private $bankAccountNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_branch_code", type="string", length=100, nullable=false)
     */
    private $bankBranchCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="uid", type="string", length=100, nullable=true)
     */
    private $uid;

    /**
     * @var string|null
     *
     * @ORM\Column(name="terms", type="text", length=65535, nullable=true)
     */
    private $terms = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="google_review_link", type="string", length=100, nullable=true)
     */
    private $googleReviewLink;

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
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     */
    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return string
     */
    public function getAdminEmail(): string
    {
        return $this->adminEmail;
    }

    /**
     * @param string $adminEmail
     */
    public function setAdminEmail(string $adminEmail): void
    {
        $this->adminEmail = $adminEmail;
    }

    /**
     * @return string
     */
    public function getServerName(): string
    {
        return $this->serverName;
    }

    /**
     * @param string $serverName
     */
    public function setServerName(string $serverName): void
    {
        $this->serverName = $serverName;
    }

    /**
     * @return string
     */
    public function getBankName(): string
    {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     */
    public function setBankName(string $bankName): void
    {
        $this->bankName = $bankName;
    }

    /**
     * @return string
     */
    public function getBankAccountType(): string
    {
        return $this->bankAccountType;
    }

    /**
     * @param string $bankAccountType
     */
    public function setBankAccountType(string $bankAccountType): void
    {
        $this->bankAccountType = $bankAccountType;
    }

    /**
     * @return string
     */
    public function getBankAccountNumber(): string
    {
        return $this->bankAccountNumber;
    }

    /**
     * @param string $bankAccountNumber
     */
    public function setBankAccountNumber(string $bankAccountNumber): void
    {
        $this->bankAccountNumber = $bankAccountNumber;
    }

    /**
     * @return string
     */
    public function getBankBranchCode(): string
    {
        return $this->bankBranchCode;
    }

    /**
     * @param string $bankBranchCode
     */
    public function setBankBranchCode(string $bankBranchCode): void
    {
        $this->bankBranchCode = $bankBranchCode;
    }

    /**
     * @return string|null
     */
    public function getUid(): ?string
    {
        return $this->uid;
    }

    /**
     * @param string|null $uid
     */
    public function setUid(?string $uid): void
    {
        $this->uid = $uid;
    }

    /**
     * @return string|null
     */
    public function getTerms(): ?string
    {
        return $this->terms;
    }

    /**
     * @param string|null $terms
     */
    public function setTerms(?string $terms): void
    {
        $this->terms = $terms;
    }

    /**
     * @return string|null
     */
    public function getGoogleReviewLink(): ?string
    {
        return $this->googleReviewLink;
    }

    /**
     * @param string|null $googleReviewLink
     */
    public function setGoogleReviewLink(?string $googleReviewLink): void
    {
        $this->googleReviewLink = $googleReviewLink;
    }


}
