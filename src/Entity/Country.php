<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "countries")]
#[ORM\Entity]
#[ORM\Index(name: "symbol_idx", columns: ["symbol"])]
#[UniqueEntity(fields: ["symbol"])]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Country
{
    use TimestampableEntity;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(["country"])]
    #[Gedmo\Versioned]
    private bool $isActive;

    #[ORM\Column(length: 2)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 2)]
    #[Groups(["country"])]
    #[Gedmo\Versioned]
    private ?string $symbol = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(["country"])]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["country"])]
    private string $uuid;

    public function __construct()
    {
        $this->isActive = false;
        $this->uuid = Uuid::uuid4()->toString();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'isActive' => $this->getIsActive(),
            'name' => $this->getName(),
            'symbol' => $this->getSymbol(),
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(?string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }
}
