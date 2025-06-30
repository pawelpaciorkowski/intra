<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "employees")]
#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Employee implements MessageSerializerInterface, IndexInterface
{
    use TimestampableEntity;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(["employee"])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["employee"])]
    private ?int $externalId = null;

    #[ORM\Column(length: 100)]
    #[Groups(["employee"])]
    private ?string $surname = null;

    #[ORM\Column(length: 250, nullable: true)]
    #[Groups(["employee"])]
    private ?string $email = null;

    #[Groups(["phones"])]
    #[ORM\OneToMany(targetEntity: "Phone", mappedBy: "employee", cascade: ["persist"], orphanRemoval: true)]
    #[Assert\Valid]
    private Collection $phones;

    #[ORM\Column(type: "guid", unique: true)]
    private string $uuid;

    #[ORM\Column(length: 128, nullable: true)]
    #[Groups(["employee"])]
    private ?string $position = null;

    #[ManyToMany(targetEntity: "Department")]
    #[JoinTable(name: "employees_to_departments", joinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ], inverseJoinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ])]
    private Collection $departments;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->phones = new ArrayCollection();
        $this->departments = new ArrayCollection();
    }

    public function updateFromEmployee(\App\Services\Employee\Entity\Employee $employee): self
    {
        $this
            ->setName($employee->getName())
            ->setSurname($employee->getSurname())
            ->setEmail($employee->getEmail())
            ->setPosition($employee->getPosition())
            ->setExternalId($employee->getExternalId());

        return $this;
    }

    public static function getSerializedGroups(): array
    {
        return [
            'phone',
            'department',
            'employee',
        ];
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

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

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

    public function getPhones(): Collection
    {
        return $this->phones;
    }

    public function addPhone(Phone $phone): self
    {
        if (!$this->phones->contains($phone)) {
            $this->phones[] = $phone;
            $phone->setEmployee($this);
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        if ($this->phones->removeElement($phone)) {
            // set the owning side to null (unless already changed)
            if ($phone->getEmployee() === $this) {
                $phone->setEmployee(null);
            }
        }

        return $this;
    }

    public function removeAllPhones(): self
    {
        foreach ($this->phones as $phone) {
            $this->phones->removeElement($phone);
        }

        return $this;
    }

    public function addDepartment(Department $Department): self
    {
        if (!$this->departments->contains($Department)) {
            $this->departments[] = $Department;
        }

        return $this;
    }

    public function removeDepartment(Department $Department): void
    {
        $this->departments->removeElement($Department);
    }

    public function getDepartments(): ?Collection
    {
        return $this->departments;
    }

    public function getDataForIndex(): array
    {
        $phones = [];
        foreach ($this->phones as $phone) {
            $phones[] = $phone->getNumber();
        }

        return array_filter([
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'position' => $this->position,
            'phones' => implode(', ', $phones),
        ], static function ($e) {return !empty($e);});
    }

    public function getNameForIndex(): string
    {
        return $this->name.' '.$this->surname;
    }

    public function getDescriptionForIndex(): ?string
    {
        $phones = [];
        foreach ($this->phones as $phone) {
            $phones[] = $phone->getNumber();
        }

        $data = [];

        if ($this->email) {
            $data[] = 'email: '.$this->email;
        }

        if ($this->position) {
            $data[] = 'stanowisko: '.$this->position;
        }

        if ($phones) {
            $data[] = 'tel.: '.implode(', ', $phones);
        }

        return implode(', ', $data);
    }

    public function getIsActive(): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        return 40;
    }

    public function getIsIndex(): bool
    {
        return true;
    }

    public function getExternalId(): ?int
    {
        return $this->externalId;
    }

    public function setExternalId(?int $externalId): static
    {
        $this->externalId = $externalId;

        return $this;
    }
}
