<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TicketRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Table(name: "tickets")]
#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\Index(name: "ticket_idx", columns: ["ticket"])]
#[UniqueEntity(fields: ["ticket"])]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Ticket
{
    use TimestampableEntity;

    public const int TYPE_PASSWORD = 1;

    public const int VALID_TIMEOUT = 604800;
    public const int LENGTH = 40;

    #[ORM\ManyToOne(cascade: ["persist"])]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[Gedmo\Versioned]
    private ?User $user = null;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 128, unique: true)]
    #[Gedmo\Versioned]
    private ?string $ticket = null;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private bool $isValid;

    #[ORM\Column(type: "smallint")]
    #[Gedmo\Versioned]
    private ?int $type = null;

    #[ORM\Column(type: "datetime")]
    #[Gedmo\Versioned]
    private ?DateTimeInterface $validUntil = null;

    public function __construct()
    {
        $this->isValid = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicket(): ?string
    {
        return $this->ticket;
    }

    public function setTicket(string $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getIsValid(): ?bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): self
    {
        $this->isValid = $isValid;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValidUntil(): ?DateTimeInterface
    {
        return $this->validUntil;
    }

    public function setValidUntil(DateTimeInterface $validUntil): self
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
