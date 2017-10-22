<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Validation\MeetupGroup;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Assert\GroupSequence({"GroupRequest", "Heavy"})
 * @ORM\Entity
 * @UniqueEntity("urlname")
 */
class GroupRequest
{
    public const STATUS_OPEN = 'open';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column
     * @Assert\NotBlank
     */
    private $name = '';

    /**
     * @ORM\Column
     * @Assert\Email
     */
    private $email = '';

    /**
     * @ORM\Column
     */
    private $token;

    /**
     * @ORM\Column
     * @Assert\NotBlank
     * @MeetupGroup(groups={"Heavy"})
     */
    private $urlname = '';

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     * @Assert\Length(min=50)
     */
    private $comment = '';

    /**
     * @ORM\Column
     */
    private $status = self::STATUS_OPEN;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->token = hash('sha256', random_bytes(8));
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUrlname(): string
    {
        return $this->urlname;
    }

    public function setUrlname(string $urlname)
    {
        $this->urlname = $urlname;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment)
    {
        $this->comment = $comment;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
