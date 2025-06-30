<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\IndexRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "index_table")]
#[ORM\Entity(repositoryClass: IndexRepository::class)]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
class Index
{
    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 256)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'text')]
    private ?string $objectData = null;

    #[ORM\Column(type: 'text')]
    private ?string $objectClass = null;

    #[ORM\Column(length: 1024)]
    private ?string $url = null;

    #[ORM\Column]
    private ?int $objectId = null;

    #[ORM\Column]
    private ?int $priority = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getObjectData(): ?string
    {
        return $this->objectData;
    }

    public function setObjectData(?string $objectData): self
    {
        $this->objectData = $objectData;

        return $this;
    }

    public function getObjectClass(): ?string
    {
        return $this->objectClass;
    }

    public function setObjectClass(?string $objectClass): self
    {
        $this->objectClass = $objectClass;

        return $this;
    }

    public function getObjectId(): ?int
    {
        return $this->objectId;
    }

    public function setObjectId(?int $objectId): self
    {
        $this->objectId = $objectId;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
