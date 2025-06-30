<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "customer_services")]
#[ORM\Entity]
#[UniqueEntity(fields: ["name"])]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class CustomerService
{
    use TimestampableEntity;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(["customerService"])]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(length: 250)]
    #[Assert\Email]
    #[Assert\Length(max: 250)]
    #[Assert\NotBlank]
    #[Groups(["customerService"])]
    #[Gedmo\Versioned]
    private ?string $email = null;

    #[ORM\OneToMany(targetEntity: "Phone", mappedBy: "customerService", cascade: ["persist"], orphanRemoval: true)]
    #[Groups(["phone"])]
    #[Assert\Valid]
    private Collection $phones;

    #[ORM\Column(type: "text", nullable: true)]
    #[Groups(["customerService"])]
    #[Gedmo\Versioned]
    private ?string $description = null;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["customerService"])]
    private string $uuid;

    #[ManyToMany(targetEntity: "Province")]
    #[JoinTable(name: "customer_services_to_provinces", joinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ], inverseJoinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        )
    ])]
    #[ORM\OrderBy(["name" => "ASC"])]
    #[Groups(["province"])]
    private Collection $provinces;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->phones = new ArrayCollection();
        $this->provinces = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
            $phone->setCustomerService($this);
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        if ($this->phones->removeElement($phone)) {
            // set the owning side to null (unless already changed)
            if ($phone->getCustomerService() === $this) {
                $phone->setCustomerService(null);
            }
        }

        return $this;
    }

    public function getProvinces(): Collection
    {
        return $this->provinces;
    }

    public function addProvince(Province $province): self
    {
        if (!$this->provinces->contains($province)) {
            $this->provinces[] = $province;
        }

        return $this;
    }

    public function removeProvince(Province $province): self
    {
        $this->provinces->removeElement($province);

        return $this;
    }
}
