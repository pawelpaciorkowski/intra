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

#[ORM\Table(name: "database_definitions")]
#[ORM\Entity]
#[UniqueEntity(fields: ["laboratory", "ip", "name"], message: "Laboratorium posiada już taką bazę danych")]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Database
{
    use TimestampableEntity;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: "databases")]
    #[ORM\JoinColumn]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private ?Laboratory $laboratory;

    #[ORM\Column(length: 15, nullable: false)]
    #[Assert\Length(max: 15)]
    #[Assert\NotBlank]
    #[Groups(["database"])]
    #[Gedmo\Versioned]
    private ?string $ip = null;

    #[ORM\Column(length: 128, nullable: false)]
    #[Assert\Length(max: 128)]
    #[Assert\NotBlank]
    #[Groups(["database"])]
    #[Gedmo\Versioned]
    private ?string $name;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["database"])]
    private string $uuid;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

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

    public function getLaboratory(): ?Laboratory
    {
        return $this->laboratory;
    }

    public function setLaboratory(?Laboratory $laboratory): self
    {
        $this->laboratory = $laboratory;

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
