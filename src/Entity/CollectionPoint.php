<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CollectionPointRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "collection_points")]
#[ORM\Entity(repositoryClass: CollectionPointRepository::class)]
#[ORM\Index(name: "street_marcel_idx", columns: ["street", "marcel"])]
#[UniqueEntity(fields: ["marcel"])]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class CollectionPoint implements IndexInterface
{
    use TimestampableEntity;

    public const array IS_CHILDREN_AGE_OPTIONS = [
        3 => '3 miesiące',
        36 => '3 lata',
    ];

    #[ORM\ManyToOne(inversedBy: "collectionPoints")]
    #[ORM\JoinColumn]
    #[Groups(["user"])]
    #[Gedmo\Versioned]
    private ?User $user;

    #[ORM\ManyToOne(inversedBy: "collectionPoints2")]
    #[ORM\JoinColumn]
    #[Groups(["user"])]
    #[Gedmo\Versioned]
    private ?User $user2;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 256)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[Gedmo\Versioned]
    #[Groups(["collectionPoint"])]
    #[ORM\Column]
    private bool $isActive;

    #[Gedmo\Versioned]
    #[Groups(["collectionPoint"])]
    #[ORM\Column]
    private bool $isWeb;

    #[Gedmo\Versioned]
    #[Groups(["collectionPoint"])]
    #[ORM\Column]
    private bool $isShop;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?string $marcel = null;

    #[ORM\Column(length: 6, nullable: true)]
    #[Assert\Length(max: 6)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?string $mpk = null;

    #[ORM\Column(length: 256, nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?string $street = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    #[Groups(["streetType"])]
    #[Gedmo\Versioned]
    private ?StreetType $streetType;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    #[Groups(["city"])]
    #[Gedmo\Versioned]
    private ?City $city;

    #[ORM\Column(length: 6, nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?string $postalCode = null;

    #[ORM\OneToMany(targetEntity: "Phone", mappedBy: "collectionPoint", cascade: ["persist"], orphanRemoval: true)]
    #[Assert\Valid]
    #[Groups(["phone"])]
    private Collection $phones;

    #[ORM\Column(nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?bool $isInternet = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?bool $isParking = null;

    #[ORM\Column]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private bool $isForDisabled;

    #[ORM\Column(nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?bool $isChildren = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?bool $isDermatofit = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?bool $isSwab = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?bool $isGynecology = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?string $additionalInfo = null;

    #[ORM\Column(type: "decimal", precision: 8, scale: 5, nullable: true)]
    #[Assert\Range(notInRangeMessage: "Dozwolony zakres {{ min }} do {{ max }}", min: -90, max: 90, )]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?string $latitude = null;

    #[ORM\Column(type: "decimal", precision: 8, scale: 5, nullable: true)]
    #[Assert\Range(notInRangeMessage: "Dozwolony zakres {{ min }} do {{ max }}", min: -180, max: 180, )]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?string $longitude = null;

    #[ORM\Column(length: 256, nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?string $email = null;

    #[ORM\ManyToOne(inversedBy: "collectionPoints")]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private ?Laboratory $laboratory;

    #[ORM\OneToMany(targetEntity: "Period", mappedBy: "collectionPoint", cascade: ["persist"], orphanRemoval: true)]
    #[Assert\Valid]
    #[Groups(["period"])]
    private Collection $periods;

    #[ORM\OneToMany(targetEntity: "Calendar", mappedBy: "collectionPoint", cascade: ["persist"], orphanRemoval: true)]
    #[Assert\Valid]
    #[Groups(["calendar"])]
    private Collection $calendars;

    #[ORM\OneToMany(targetEntity: "CollectionPointCloseDate", mappedBy: "collectionPoint", cascade: ["persist"], orphanRemoval: true)]
    #[ORM\OrderBy(["startAt" => "ASC"])]
    #[Groups(["collectionPointCloseDate"])]
    #[Assert\Valid]
    private Collection $collectionPointCloseDates;

    #[ORM\OneToMany(targetEntity: "CollectionPointExtraDate", mappedBy: "collectionPoint", cascade: ["persist"], orphanRemoval: true)]
    #[ORM\OrderBy(["startAt" => "ASC"])]
    #[Groups(["collectionPointExtraDate"])]
    #[Assert\Valid]
    private Collection $collectionPointExtraDates;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    #[Groups(["collectionPointClassification"])]
    #[Gedmo\Versioned]
    private ?CollectionPointClassification $collectionPointClassification;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    #[Groups(["collectionPointLocation"])]
    #[Gedmo\Versioned]
    private ?CollectionPointLocation $collectionPointLocation;

    #[ORM\ManyToOne(targetEntity: "CollectionPointPartner")]
    #[ORM\JoinColumn(name: "collection_point_partner_id", referencedColumnName: "id")]
    #[Groups(["collectionPointPartner"])]
    #[Gedmo\Versioned]
    private ?CollectionPointPartner $collectionPointPartner;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    #[Groups(["collectionPointType"])]
    #[Gedmo\Versioned]
    private ?CollectionPointType $collectionPointType;

    #[ORM\Column(type: 'smallint')]
    #[Assert\Range(notInRangeMessage: "Dozwolony zakres {{ min }} do {{ max }}", min: 0, max: 999, )]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private int $takingSamples;

    #[ORM\Column(type: 'smallint')]
    #[Assert\Range(notInRangeMessage: "Dozwolony zakres {{ min }} do {{ max }}", min: 0, max: 999, )]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private int $registrants;

    #[ORM\Column(length: 256, nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?string $priceList = null;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["collectionPoint"])]
    private string $uuid;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    #[Assert\Expression("this.getCollectionPointAlternative() == null or this.getCollectionPointAlternative() !- this", message: "Punkt pobrań nie może wskazywać sam na siebie")]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?CollectionPoint $collectionPointAlternative;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\Expression("this.getIsActive() == false or this.getOpenAt() !- null", message: "Jeżeli punkt jest aktywny, musisz określić datę kiedy został uruchomiony")]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?DateTimeInterface $openAt = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\Expression("this.getIsActive() == false or this.getSignedAt() !- null", message: "Jeżeli punkt jest aktywny, musisz określić datę kiedy została podpisana umowa")]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?DateTimeInterface $signedAt = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\Expression("this.getOpenAt() == null or this.getCloseAt() == null or this.getOpenAt() <: this.getCloseAt()", message: "Data zamknięcia nie może być wcześniejsza od daty uruchomienia")]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?DateTimeInterface $closeAt = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?string $internalInfo = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?bool $isCard = null;

    #[ORM\Column(length: 256, nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?string $addressInfo = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?bool $isChildrenConfirm = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(notInRangeMessage: "Dozwolony zakres {{ min }} do {{ max }}", min: 0, max: 648, )]
    #[Assert\Expression("!this.getIsChildren() or (this.getIsChildren() and this.getIsChildrenAge() > 0)", message: "Należy określić wiek dziecka")]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?int $isChildrenAge = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?bool $isDermatofitConfirm = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?bool $isGynecologyConfirm = null;

    #[ORM\Column]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private bool $isContest;

    #[ORM\Column]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private bool $isCovidPrivate;

    #[Groups(["collectionPoint"])]
    private array $periodsSimple;

    #[Groups(["collectionPoint"])]
    private ?bool $isCovid = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?string $walk3d = null;

    #[ORM\Column(name: 'is40plus')]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private bool $is40Plus;

    public function __construct()
    {
        $this->isActive = true;
        $this->isWeb = false;
        $this->isShop = false;
        $this->periods = new ArrayCollection();
        $this->takingSamples = 0;
        $this->registrants = 0;
        $this->uuid = Uuid::uuid4()->toString();
        $this->collectionPointCloseDates = new ArrayCollection();
        $this->collectionPointExtraDates = new ArrayCollection();
        $this->calendars = new ArrayCollection();
        $this->isContest = false;
        $this->isCovidPrivate = false;
        $this->isParking = false;
        $this->isForDisabled = false;
        $this->is40Plus = false;
        $this->phones = new ArrayCollection();
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

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getMarcel(): ?string
    {
        return $this->marcel;
    }

    public function setMarcel(?string $marcel): self
    {
        $this->marcel = $marcel;

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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

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

    public function getIsInternet(): ?bool
    {
        return $this->isInternet;
    }

    public function setIsInternet(?bool $isInternet): self
    {
        $this->isInternet = $isInternet;

        return $this;
    }

    public function getIsChildren(): ?bool
    {
        return $this->isChildren;
    }

    public function setIsChildren(?bool $isChildren): self
    {
        $this->isChildren = $isChildren;

        return $this;
    }

    public function getIsDermatofit(): ?bool
    {
        return $this->isDermatofit;
    }

    public function setIsDermatofit(?bool $isDermatofit): self
    {
        $this->isDermatofit = $isDermatofit;

        return $this;
    }

    public function getIsSwab(): ?bool
    {
        return $this->isSwab;
    }

    public function setIsSwab(?bool $isSwab): self
    {
        $this->isSwab = $isSwab;

        return $this;
    }

    public function getIsGynecology(): ?bool
    {
        return $this->isGynecology;
    }

    public function setIsGynecology(?bool $isGynecology): self
    {
        $this->isGynecology = $isGynecology;

        return $this;
    }

    public function getAdditionalInfo(): ?string
    {
        return $this->additionalInfo;
    }

    public function setAdditionalInfo(?string $additionalInfo): self
    {
        $this->additionalInfo = $additionalInfo;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getTakingSamples(): ?int
    {
        return $this->takingSamples;
    }

    public function setTakingSamples(int $takingSamples): self
    {
        $this->takingSamples = $takingSamples;

        return $this;
    }

    public function getRegistrants(): ?int
    {
        return $this->registrants;
    }

    public function setRegistrants(int $registrants): self
    {
        $this->registrants = $registrants;

        return $this;
    }

    public function getPriceList(): ?string
    {
        return $this->priceList;
    }

    public function setPriceList(?string $priceList): self
    {
        $this->priceList = $priceList;

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

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

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

    public function getPeriods(): Collection
    {
        return $this->periods;
    }

    public function addPeriod(Period $period): self
    {
        if (!$this->periods->contains($period)) {
            $this->periods[] = $period;
            $period->setCollectionPoint($this);
        }

        return $this;
    }

    public function removePeriod(Period $period): self
    {
        if ($this->periods->contains($period)) {
            $this->periods->removeElement($period);
            // set the owning side to null (unless already changed)
            if ($period->getCollectionPoint() === $this) {
                $period->setCollectionPoint(null);
            }
        }

        return $this;
    }

    public function getCollectionPointClassification(): ?CollectionPointClassification
    {
        return $this->collectionPointClassification;
    }

    public function setCollectionPointClassification(
        ?CollectionPointClassification $collectionPointClassification
    ): self {
        $this->collectionPointClassification = $collectionPointClassification;

        return $this;
    }

    public function getCollectionPointLocation(): ?CollectionPointLocation
    {
        return $this->collectionPointLocation;
    }

    public function setCollectionPointLocation(?CollectionPointLocation $collectionPointLocation): self
    {
        $this->collectionPointLocation = $collectionPointLocation;

        return $this;
    }

    public function getCollectionPointPartner(): ?CollectionPointPartner
    {
        return $this->collectionPointPartner;
    }

    public function setCollectionPointPartner(?CollectionPointPartner $collectionPointPartner): self
    {
        $this->collectionPointPartner = $collectionPointPartner;

        return $this;
    }

    public function getCollectionPointType(): ?CollectionPointType
    {
        return $this->collectionPointType;
    }

    public function setCollectionPointType(?CollectionPointType $collectionPointType): self
    {
        $this->collectionPointType = $collectionPointType;

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

    public function getCollectionPointCloseDates(): Collection
    {
        return $this->collectionPointCloseDates;
    }

    public function addCollectionPointCloseDate(CollectionPointCloseDate $collectionPointCloseDate): self
    {
        if (!$this->collectionPointCloseDates->contains($collectionPointCloseDate)) {
            $this->collectionPointCloseDates[] = $collectionPointCloseDate;
            $collectionPointCloseDate->setCollectionPoint($this);
        }

        return $this;
    }

    public function removeCollectionPointCloseDate(CollectionPointCloseDate $collectionPointCloseDate): self
    {
        if ($this->collectionPointCloseDates->contains($collectionPointCloseDate)) {
            $this->collectionPointCloseDates->removeElement($collectionPointCloseDate);
            // set the owning side to null (unless already changed)
            if ($collectionPointCloseDate->getCollectionPoint() === $this) {
                $collectionPointCloseDate->setCollectionPoint(null);
            }
        }

        return $this;
    }

    public function getIsWeb(): ?bool
    {
        return $this->isWeb;
    }

    public function setIsWeb(bool $isWeb): self
    {
        $this->isWeb = $isWeb;

        return $this;
    }

    public function getCollectionPointAlternative(): ?self
    {
        return $this->collectionPointAlternative;
    }

    public function setCollectionPointAlternative(?self $collectionPointAlternative): self
    {
        $this->collectionPointAlternative = $collectionPointAlternative;

        return $this;
    }

    public function getIsShop(): ?bool
    {
        return $this->isShop;
    }

    public function setIsShop(bool $isShop): self
    {
        $this->isShop = $isShop;

        return $this;
    }

    public function getOpenAt(): ?DateTimeInterface
    {
        return $this->openAt;
    }

    public function setOpenAt(?DateTimeInterface $openAt): self
    {
        $this->openAt = $openAt;

        return $this;
    }

    public function getCloseAt(): ?DateTimeInterface
    {
        return $this->closeAt;
    }

    public function setCloseAt(?DateTimeInterface $closeAt): self
    {
        $this->closeAt = $closeAt;

        return $this;
    }

    public function getCalendars(): Collection
    {
        return $this->calendars;
    }

    public function addCalendar(Calendar $calendar): self
    {
        if (!$this->calendars->contains($calendar)) {
            $this->calendars[] = $calendar;
            $calendar->setCollectionPoint($this);
        }

        return $this;
    }

    public function removeCalendar(Calendar $calendar): self
    {
        if ($this->calendars->removeElement($calendar)) {
            // set the owning side to null (unless already changed)
            if ($calendar->getCollectionPoint() === $this) {
                $calendar->setCollectionPoint(null);
            }
        }

        return $this;
    }

    public function getInternalInfo(): ?string
    {
        return $this->internalInfo;
    }

    public function setInternalInfo(?string $internalInfo): self
    {
        $this->internalInfo = $internalInfo;

        return $this;
    }

    public function getIsCard(): ?bool
    {
        return $this->isCard;
    }

    public function setIsCard(?bool $isCard): self
    {
        $this->isCard = $isCard;

        return $this;
    }

    public function getAddressInfo(): ?string
    {
        return $this->addressInfo;
    }

    public function setAddressInfo(?string $addressInfo): self
    {
        $this->addressInfo = $addressInfo;

        return $this;
    }

    public function getIsChildrenConfirm(): ?bool
    {
        return $this->isChildrenConfirm;
    }

    public function setIsChildrenConfirm(?bool $isChildrenConfirm): self
    {
        $this->isChildrenConfirm = $isChildrenConfirm;

        return $this;
    }

    public function getIsChildrenAge(): ?int
    {
        return $this->isChildrenAge;
    }

    public function setIsChildrenAge(?int $isChildrenAge): self
    {
        $this->isChildrenAge = $isChildrenAge;

        return $this;
    }

    public function getIsDermatofitConfirm(): ?bool
    {
        return $this->isDermatofitConfirm;
    }

    public function setIsDermatofitConfirm(?bool $isDermatofitConfirm): self
    {
        $this->isDermatofitConfirm = $isDermatofitConfirm;

        return $this;
    }

    public function getIsGynecologyConfirm(): ?bool
    {
        return $this->isGynecologyConfirm;
    }

    public function setIsGynecologyConfirm(?bool $isGynecologyConfirm): self
    {
        $this->isGynecologyConfirm = $isGynecologyConfirm;

        return $this;
    }

    public function getIsContest(): ?bool
    {
        return $this->isContest;
    }

    public function setIsContest(bool $isContest): self
    {
        $this->isContest = $isContest;

        return $this;
    }

    public function getIsCovidPrivate(): ?bool
    {
        return $this->isCovidPrivate;
    }

    public function setIsCovidPrivate(bool $isCovidPrivate): self
    {
        $this->isCovidPrivate = $isCovidPrivate;

        return $this;
    }

    public function getIsParking(): ?bool
    {
        return $this->isParking;
    }

    public function setIsParking(?bool $isParking): self
    {
        $this->isParking = $isParking;

        return $this;
    }

    public function getIsForDisabled(): ?bool
    {
        return $this->isForDisabled;
    }

    public function setIsForDisabled(?bool $isForDisabled): self
    {
        $this->isForDisabled = $isForDisabled;

        return $this;
    }

    public function getPeriodsSimple(): array
    {
        $temporaryData = [];
        $dayOfWeeks = [];

        // step 1: prepare temporary array
        foreach ($this->periods as $period) {
            $dayOfWeeks[$period->getDayOfWeek()->getId()] = $period->getDayOfWeek()->getShortName();
            $temporaryData[$period->getType()][$period->getDayOfWeek()->getId()][] = !$period->getIsAllDay(
            ) ? $period->getStartAt()->format('H:i') . '-' . $period->getEndAt()->format('H:i') : 'całodobowo';
        }

        // step 2: sort everything and combine hours
        ksort($temporaryData);
        foreach ($temporaryData as $type => $typeContent) {
            foreach ($typeContent as $dayOfWeek => $periods) {
                usort($periods, static function ($a, $b) {
                    return $a <=> $b;
                });

                $temporaryData[$type][$dayOfWeek] = implode(', ', $periods);
            }
            ksort($temporaryData[$type]);
        }

        // step 3: combine days and prepare return structure
        $returnData = [];
        foreach ($temporaryData as $type => $typeContent) {
            $dataRow = [
                'type' => $type,
                'name' => Period::TYPE_NAMES[$type],
                'periods' => [],
            ];

            foreach ($typeContent as $dayOfWeek => $period) {
                for ($i = 0, $iMax = count($dataRow['periods']); $i < $iMax; ++$i) {
                    if ($period === $dataRow['periods'][$i]['period']) {
                        if (substr($dataRow['periods'][$i]['dayOfWeek'], -1) === (string)($dayOfWeek - 1)) {
                            if ('-' === $dataRow['periods'][$i]['dayOfWeek'][strlen(
                                $dataRow['periods'][$i]['dayOfWeek']
                            ) - 2]) {
                                $dataRow['periods'][$i]['dayOfWeek'] = substr(
                                    $dataRow['periods'][$i]['dayOfWeek'],
                                    0,
                                    -1
                                ) . $dayOfWeek;
                            } else {
                                $dataRow['periods'][$i]['dayOfWeek'] .= '-' . $dayOfWeek;
                            }
                            continue 2;
                        }
                        $dataRow['periods'][$i]['dayOfWeek'] .= ', ' . $dayOfWeek;
                        continue 2;
                    }
                }

                $dataRow['periods'][] = [
                    'dayOfWeek' => (string)$dayOfWeek,
                    'period' => $period,
                ];
            }

            for ($i = 0, $iMax = count($dataRow['periods']); $i < $iMax; ++$i) {
                $dataRow['periods'][$i]['dayOfWeek'] = str_replace(
                    array_keys($dayOfWeeks),
                    array_values($dayOfWeeks),
                    $dataRow['periods'][$i]['dayOfWeek']
                );
            }

            $returnData[] = $dataRow;
        }

        return $returnData;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWalk3d(): ?string
    {
        return $this->walk3d;
    }

    public function setWalk3d(?string $walk3d): self
    {
        $this->walk3d = $walk3d;

        return $this;
    }

    public function getUser2(): ?User
    {
        return $this->user2;
    }

    public function setUser2(?User $user2): self
    {
        $this->user2 = $user2;

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
            $phone->setCollectionPoint($this);
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        if ($this->phones->removeElement($phone)) {
            // set the owning side to null (unless already changed)
            if ($phone->getCollectionPoint() === $this) {
                $phone->setCollectionPoint(null);
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

    public function isCovid(): bool
    {
        return $this->isCovidPrivate;
    }

    public function getIs40Plus(): ?bool
    {
        return $this->is40Plus;
    }

    public function setIs40Plus(bool $is40Plus): self
    {
        $this->is40Plus = $is40Plus;

        return $this;
    }

    public function getCollectionPointExtraDates(): Collection
    {
        return $this->collectionPointExtraDates;
    }

    public function addCollectionPointExtraDate(CollectionPointExtraDate $collectionPointExtraDate): self
    {
        if (!$this->collectionPointExtraDates->contains($collectionPointExtraDate)) {
            $this->collectionPointExtraDates[] = $collectionPointExtraDate;
            $collectionPointExtraDate->setCollectionPoint($this);
        }

        return $this;
    }

    public function removeCollectionPointExtraDate(CollectionPointExtraDate $collectionPointExtraDate): self
    {
        if ($this->collectionPointExtraDates->removeElement($collectionPointExtraDate)) {
            // set the owning side to null (unless already changed)
            if ($collectionPointExtraDate->getCollectionPoint() === $this) {
                $collectionPointExtraDate->setCollectionPoint(null);
            }
        }

        return $this;
    }

    public function getSignedAt(): ?DateTimeInterface
    {
        return $this->signedAt;
    }

    public function setSignedAt(?DateTimeInterface $signedAt): self
    {
        $this->signedAt = $signedAt;

        return $this;
    }

    public function getDataForIndex(): array
    {
        $phones = [];
        foreach ($this->phones as $phone) {
            $phones[] = $phone->getNumber();
        }

        return array_filter([
            'name' => $this->name,
            'marcel' => $this->marcel,
            'mpk' => $this->mpk,
            'street' => $this->street,
            'city' => $this->city?->getName(),
            'postalCode' => $this->postalCode,
            'additionalInfo' => $this->additionalInfo,
            'internalInfo' => $this->internalInfo,
            'email' => $this->email,
            'addressInfo' => $this->addressInfo,
            'phones' => implode(', ', $phones),
        ], static function ($e) {return !empty($e);});
    }

    public function getNameForIndex(): string
    {
        return $this->name;
    }

    public function getDescriptionForIndex(): ?string
    {
        $phones = [];
        foreach ($this->phones as $phone) {
            $phones[] = $phone->getNumber();
        }

        $data = [];

        if ($this->marcel) {
            $data[] = 'symbol: '.$this->marcel;
        }

        if ($this->mpk) {
            $data[] = 'mpk: '.$this->mpk;
        }

        if ($this->city) {
            $data[] = 'miejscowość: '.$this->city->getName();
        }

        if ($this->street) {
            $data[] = 'adres: '.$this->street;
        }

        if ($this->postalCode) {
            $data[] = 'kod pocztowy: '.$this->postalCode;
        }

        if ($this->email) {
            $data[] = 'email: '.$this->email;
        }

        if ($phones) {
            $data[] = 'tel.: '.implode(', ', $phones);
        }

        return implode(', ', $data);
    }

    public function getPriority(): int
    {
        return 30;
    }

    public function getIsIndex(): bool
    {
        return true;
    }
}
