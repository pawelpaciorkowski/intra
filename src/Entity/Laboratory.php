<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LaboratoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "laboratories")]
#[ORM\Entity(repositoryClass: LaboratoryRepository::class)]
#[UniqueEntity(fields: ["symbol"])]
#[UniqueEntity(fields: ["organizationalUnit"])]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Laboratory
{
    use TimestampableEntity;

    public const array IMPORT_SKIP_SYMBOLS = ['XXX'];

    #[ORM\ManyToOne(inversedBy: "laboratories")]
    #[ORM\JoinColumn]
    #[Groups(["user"])]
    #[Gedmo\Versioned]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: "Database", mappedBy: "laboratory", cascade: ["persist"], orphanRemoval: true)]
    #[ORM\OrderBy(["ip" => "ASC", "name" => "ASC"])]
    #[Groups(["database"])]
    #[Assert\Valid]
    private Collection $databases;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    #[Groups(["region"])]
    #[Gedmo\Versioned]
    private ?Region $region = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    #[Groups(["billingCenter"])]
    #[Gedmo\Versioned]
    private ?BillingCenter $billingCenter = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    #[Groups(["customerService"])]
    #[Gedmo\Versioned]
    private ?CustomerService $customerService = null;

    #[ORM\OneToMany(targetEntity: "Lab", mappedBy: "laboratory")]
    #[ORM\OrderBy(["name" => "ASC"])]
    private Collection $labs;

    #[ORM\OneToMany(targetEntity: "CollectionPoint", mappedBy: "laboratory")]
    #[ORM\OrderBy(["street" => "ASC", "marcel" => "ASC"])]
    private Collection $collectionPoints;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private ?string $symbol = null;

    #[ORM\Column(length: 3)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 3)]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private ?string $prefix = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private ?string $mpk = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    #[Groups(["city"])]
    private ?City $city = null;

    #[ORM\Column]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private bool $isHospital;

    #[ORM\Column]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private bool $isCollectionPoint;

    #[ORM\Column]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private bool $isActive;

    #[ORM\Column]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private bool $isOpenInSunday;

    #[ORM\Column]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private bool $isOpenInSaturday;

    #[ORM\Column]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private bool $isOpenInHoliday;

    #[ORM\Column(length: 256, nullable: true)]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private ?string $email = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private ?int $teryt = null;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private ?int $organizationalUnit = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private ?int $registerBook = null;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["laboratory"])]
    private string $uuid;

    #[ORM\Column(length: 6, nullable: true)]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private ?string $postalCode = null;

    #[ORM\Column(length: 256, nullable: true)]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private ?string $street = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    #[Groups(["streetType"])]
    #[Gedmo\Versioned]
    private ?StreetType $streetType = null;

    #[ORM\OneToMany(targetEntity: "Phone", mappedBy: "laboratory", cascade: ["persist"], orphanRemoval: true)]
    #[Assert\Valid]
    #[Groups(["phone"])]
    private Collection $phones;

    #[ORM\OneToMany(targetEntity: "Period", mappedBy: "laboratory", cascade: ["persist"], orphanRemoval: true)]
    #[Assert\Valid]
    #[Groups(["period"])]
    private Collection $periods;

    public function __construct()
    {
        $this->isHospital = false;
        $this->isCollectionPoint = false;
        $this->isActive = true;
        $this->isOpenInHoliday = false;
        $this->isOpenInSaturday = false;
        $this->isOpenInSunday = false;
        $this->labs = new ArrayCollection();
        $this->databases = new ArrayCollection();
        $this->collectionPoints = new ArrayCollection();
        $this->uuid = Uuid::uuid4()->toString();
        $this->phones = new ArrayCollection();
        $this->periods = new ArrayCollection();
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

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(?string $symbol): self
    {
        $this->symbol = $symbol;

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

    public function getIsHospital(): ?bool
    {
        return $this->isHospital;
    }

    public function setIsHospital(bool $isHospital): self
    {
        $this->isHospital = $isHospital;

        return $this;
    }

    public function getIsCollectionPoint(): ?bool
    {
        return $this->isCollectionPoint;
    }

    public function setIsCollectionPoint(bool $isCollectionPoint): self
    {
        $this->isCollectionPoint = $isCollectionPoint;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getBillingCenter(): ?BillingCenter
    {
        return $this->billingCenter;
    }

    public function setBillingCenter(?BillingCenter $billingCenter): self
    {
        $this->billingCenter = $billingCenter;

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

    public function getLabs(): Collection
    {
        return $this->labs;
    }

    public function addLab(Lab $lab): self
    {
        if (!$this->labs->contains($lab)) {
            $this->labs[] = $lab;
            $lab->setLaboratory($this);
        }

        return $this;
    }

    public function removeLab(Lab $lab): self
    {
        if ($this->labs->contains($lab)) {
            $this->labs->removeElement($lab);
            // set the owning side to null (unless already changed)
            if ($lab->getLaboratory() === $this) {
                $lab->setLaboratory(null);
            }
        }

        return $this;
    }

    public function getIsOpenInSunday(): ?bool
    {
        return $this->isOpenInSunday;
    }

    public function setIsOpenInSunday(bool $isOpenInSunday): self
    {
        $this->isOpenInSunday = $isOpenInSunday;

        return $this;
    }

    public function getIsOpenInSaturday(): ?bool
    {
        return $this->isOpenInSaturday;
    }

    public function setIsOpenInSaturday(bool $isOpenInSaturday): self
    {
        $this->isOpenInSaturday = $isOpenInSaturday;

        return $this;
    }

    public function getIsOpenInHoliday(): ?bool
    {
        return $this->isOpenInHoliday;
    }

    public function setIsOpenInHoliday(bool $isOpenInHoliday): self
    {
        $this->isOpenInHoliday = $isOpenInHoliday;

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(?string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getDatabases(): Collection
    {
        return $this->databases;
    }

    public function addDatabase(Database $database): self
    {
        if (!$this->databases->contains($database)) {
            $this->databases[] = $database;
            $database->setLaboratory($this);
        }

        return $this;
    }

    public function removeDatabase(Database $database): self
    {
        if ($this->databases->contains($database)) {
            $this->databases->removeElement($database);
            // set the owning side to null (unless already changed)
            if ($database->getLaboratory() === $this) {
                $database->setLaboratory(null);
            }
        }

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

    public function getCollectionPoints(): Collection
    {
        return $this->collectionPoints;
    }

    public function addCollectionPoint(CollectionPoint $collectionPoint): self
    {
        if (!$this->collectionPoints->contains($collectionPoint)) {
            $this->collectionPoints[] = $collectionPoint;
            $collectionPoint->setLaboratory($this);
        }

        return $this;
    }

    public function removeCollectionPoint(CollectionPoint $collectionPoint): self
    {
        if ($this->collectionPoints->contains($collectionPoint)) {
            $this->collectionPoints->removeElement($collectionPoint);
            // set the owning side to null (unless already changed)
            if ($collectionPoint->getLaboratory() === $this) {
                $collectionPoint->setLaboratory(null);
            }
        }

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

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

    public function getTeryt(): ?int
    {
        return $this->teryt;
    }

    public function setTeryt(?int $teryt): self
    {
        $this->teryt = $teryt;

        return $this;
    }

    public function getOrganizationalUnit(): ?int
    {
        return $this->organizationalUnit;
    }

    public function setOrganizationalUnit(?int $organizationalUnit): self
    {
        $this->organizationalUnit = $organizationalUnit;

        return $this;
    }

    public function getRegisterBook(): ?int
    {
        return $this->registerBook;
    }

    public function setRegisterBook(?int $registerBook): self
    {
        $this->registerBook = $registerBook;

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

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

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
            $phone->setLaboratory($this);
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        if ($this->phones->removeElement($phone)) {
            // set the owning side to null (unless already changed)
            if ($phone->getLaboratory() === $this) {
                $phone->setLaboratory(null);
            }
        }

        return $this;
    }

    public function getStreetType(): ?StreetType
    {
        return $this->streetType;
    }

    public function setStreetType(?StreetType $streetType): self
    {
        $this->streetType = $streetType;

        return $this;
    }

    public function getPeriods(): Collection
    {
        return $this->periods;
    }

    public function addPeriod(Period $period): self
    {
        if (!$this->periods->contains($period)) {
            $this->periods[] = $period;
            $period->setLaboratory($this);
        }

        return $this;
    }

    public function removePeriod(Period $period): self
    {
        if ($this->periods->removeElement($period)) {
            // set the owning side to null (unless already changed)
            if ($period->getLaboratory() === $this) {
                $period->setLaboratory(null);
            }
        }

        return $this;
    }
}
