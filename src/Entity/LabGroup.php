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

#[ORM\Table(name: "lab_groups")]
#[ORM\Entity]
#[UniqueEntity(fields: ["name"])]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class LabGroup
{
    use TimestampableEntity;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(["labGroup"])]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Groups(["labGroup"])]
    #[Gedmo\Versioned]
    private ?string $mpk = null;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["labGroup"])]
    private string $uuid;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getMpk(): ?string
    {
        return $this->mpk;
    }

    public function setMpk(?string $mpk): self
    {
        $this->mpk = $mpk;

        return $this;
    }
}
