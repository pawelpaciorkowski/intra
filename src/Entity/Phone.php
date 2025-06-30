<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "phones")]
#[ORM\Entity]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Phone
{
    use TimestampableEntity;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 9, nullable: false)]
    #[Assert\NotBlank(groups: ["Default", "phone"])]
    #[Assert\Length(min: 9, max: 9, groups: ["Default", "phone"])]
    #[Groups(["phone"])]
    #[Gedmo\Versioned]
    private ?string $number = null;

    #[ORM\ManyToOne(inversedBy: "phones")]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[Groups(["user"])]
    #[Gedmo\Versioned]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: "phones")]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[Groups(["employee"])]
    #[Gedmo\Versioned]
    private ?Employee $employee = null;

    #[ORM\ManyToOne(inversedBy: "phones")]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?CollectionPoint $collectionPoint = null;

    #[ORM\ManyToOne(inversedBy: "phones")]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[Groups(["customerService"])]
    #[Gedmo\Versioned]
    private ?CustomerService $customerService = null;

    #[ORM\ManyToOne(inversedBy: "phones")]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private ?Laboratory $laboratory = null;

    #[ORM\Column]
    #[Groups(["phone"])]
    #[Gedmo\Versioned]
    private bool $isVisible;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["phone"])]
    private string $uuid;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->isVisible = false;
    }

    public static function create(Employee $employee, bool $isVisible, string $number): self
    {
        $phone = new self();

        $phone
            ->setEmployee($employee)
            ->setIsVisible($isVisible)
            ->setNumber($number);

        return $phone;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCollectionPoint(): ?CollectionPoint
    {
        return $this->collectionPoint;
    }

    public function setCollectionPoint(?CollectionPoint $collectionPoint): self
    {
        $this->collectionPoint = $collectionPoint;

        return $this;
    }

    public function getCustomerService(): ?CustomerService
    {
        return $this->customerService;
    }

    public function setCustomerService(?CustomerService $customerService): self
    {
        $this->customerService = $customerService;

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

    public function getIsVisible(): ?bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(bool $isVisible): self
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function setEmployee(?Employee $employee): self
    {
        $this->employee = $employee;

        return $this;
    }
}
