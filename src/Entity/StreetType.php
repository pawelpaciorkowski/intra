<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Table(name: "street_types")]
#[ORM\Entity]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
class StreetType
{
    use TimestampableEntity;

    public const DEFAULT_UUID = 'dd9b1f99-00b8-4c57-a124-92266fd2e161';

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 128)]
    #[Groups(["streetType"])]
    private ?string $name = null;

    #[ORM\Column(length: 16)]
    #[Groups(["streetType"])]
    private ?string $short = null;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["streetType"])]
    private ?string $uuid;

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

    public function getShort(): ?string
    {
        return $this->short;
    }

    public function setShort(string $short): self
    {
        $this->short = $short;

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
