<?php

declare(strict_types=1);

namespace App\Services\Employee\Entity;

class Department implements EntityInterface
{
    private ?int $id = null;
    private string $name;
    private ?string $parent;

    public static function fromArray(array $data): static
    {
        $department = new self();

        $department
            ->setName(ucfirst(trim($data[0])))
            ->setParent(count($data) > 1 && $data[1] !== '' ? $data[1] : null);

        return $department;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function setParent(?string $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
