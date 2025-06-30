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

#[ORM\Table(name: "provinces")]
#[ORM\Entity]
#[UniqueEntity(fields: ["teryt"])]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Province
{
    use TimestampableEntity;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 128)]
    #[Assert\NotBlank]
    #[Groups(["province"])]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["province"])]
    #[Gedmo\Versioned]
    private ?int $teryt = null;

    #[ORM\Column(type: "decimal", precision: 8, scale: 5, nullable: true)]
    #[Assert\NotBlank]
    #[Groups(["province"])]
    #[Gedmo\Versioned]
    private ?string $latitude = null;

    #[ORM\Column(type: "decimal", precision: 8, scale: 5, nullable: true)]
    #[Assert\NotBlank]
    #[Groups(["province"])]
    #[Gedmo\Versioned]
    private ?string $longitude = null;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["province"])]
    private string $uuid;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank]
    #[Groups(["city"])]
    #[Gedmo\Versioned]
    private ?int $zoom = null;

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

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTeryt(): ?int
    {
        return $this->teryt;
    }

    public function setTeryt(?int $teryt): self
    {
        $this->teryt = $teryt;

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

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getZoom(): ?int
    {
        return $this->zoom;
    }

    public function setZoom(?int $zoom): self
    {
        $this->zoom = $zoom;

        return $this;
    }
}
