<?php

declare(strict_types=1);

namespace App\Services\Employee\Entity;

class Employee implements EntityInterface
{
    private ?int $id = null;
    private ?int $externalId;
    private string $name;
    private string $surname;
    private ?string $phone;
    private ?string $email;
    private ?string $position;
    private ?string $department;

    public static function fromArray(array $data): static
    {
        $employee = new self();

        $employee
            ->setExternalId((string)$data[0] !== '' ? (int)$data[0] : null)
            ->setName(ucwords(trim($data[1])))
            ->setSurname(ucwords(trim($data[2])))
            ->setPhone((string)$data[3] !== '' ? preg_replace('/\D+/', '', (string)$data[3]) : null)
            ->setEmail((string)$data[4] !== '' ? trim($data[4]) : null)
            ->setPosition((string)$data[5] !== '' ? ucfirst(trim($data[5])) : null)
            ->setDepartment((string)$data[6] !== '' ? ucwords(trim($data[6])) : null);

        return $employee;
    }

    public function getExternalId(): ?int
    {
        return $this->externalId;
    }

    public function setExternalId(?int $externalId): self
    {
        $this->externalId = $externalId;

        return $this;
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

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): self
    {
        $this->department = $department;

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
