<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LogUserAuthRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "log_user_auths")]
#[ORM\Index(name: "attempt_at_idx", columns: ["attempt_at"])]
#[ORM\Entity(repositoryClass: LogUserAuthRepository::class)]
class LogUserAuth
{
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?User $user = null;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 15)]
    #[Assert\Length(max: 15)]
    private ?string $ip = null;

    #[ORM\Column(type: "datetime")]
    private DateTimeInterface $attemptAt;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $username = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Assert\Length(max: 1024)]
    private ?string $browser = null;

    #[ORM\Column]
    private bool $isSuccess;

    public function __construct()
    {
        $this->attemptAt = new DateTime();
        $this->isSuccess = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getAttemptAt(): ?DateTimeInterface
    {
        return $this->attemptAt;
    }

    public function setAttemptAt(DateTimeInterface $attemptAt): self
    {
        $this->attemptAt = $attemptAt;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getIsSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function setIsSuccess(bool $isSuccess): self
    {
        $this->isSuccess = $isSuccess;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    public function setBrowser(?string $browser): self
    {
        $this->browser = $browser;

        return $this;
    }
}
