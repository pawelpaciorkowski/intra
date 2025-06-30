<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "periods")]
#[ORM\Entity]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Period
{
    use TimestampableEntity;

    public const TYPE_WORK = 'work';
    public const TYPE_COLLECT = 'collect';

    public const TYPE_NAMES = [
        'work' => 'Godziny pracy',
        'collect' => 'Godziny pobrań',
    ];

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: "periods")]
    #[ORM\JoinColumn]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?CollectionPoint $collectionPoint = null;

    #[ORM\ManyToOne(inversedBy: "periods")]
    #[ORM\JoinColumn]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private ?Laboratory $laboratory = null;

    #[ORM\Column(name: "start_at", type: "time", nullable: true)]
    #[Assert\Expression("this.getIsAllDay() || this.getStartAt()", message: "Godzina nie może być pusta")]
    #[Groups(["period"])]
    #[Gedmo\Versioned]
    private ?DateTimeInterface $startAt = null;

    #[ORM\Column(name: "end_at", type: "time", nullable: true)]
    #[Assert\Expression("this.getIsAllDay() || this.getStartAt() < this.getEndAt()", message: "Godzina końcowa nie może być wcześniejsza od godziny początkowej")]
    #[Assert\Expression("this.getIsAllDay() || this.getEndAt()", message: "Godzina nie może być pusta")]
    #[Groups(["period"])]
    #[Gedmo\Versioned]
    private ?DateTimeInterface $endAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    #[Groups(["dayOfWeek"])]
    #[Gedmo\Versioned]
    private ?DayOfWeek $dayOfWeek;

    #[ORM\Column(length: 10, nullable: false, columnDefinition: "ENUM('work', 'collect')")]
    #[Assert\NotBlank]
    #[Groups(["period"])]
    #[Gedmo\Versioned]
    private string $type;

    #[ORM\Column(nullable: true)]
    #[Groups(["period"])]
    #[Gedmo\Versioned]
    private ?bool $isAllDay;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["period"])]
    private string $uuid;

    public function __construct()
    {
        $this->isAllDay = false;
        $this->type = self::TYPE_WORK;
        $this->uuid = Uuid::uuid4()->toString();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartAt(): ?DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(?DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getIsAllDay(): ?bool
    {
        return $this->isAllDay;
    }

    public function setIsAllDay(?bool $isAllDay): self
    {
        $this->isAllDay = $isAllDay;

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

    public function getDayOfWeek(): ?DayOfWeek
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(?DayOfWeek $dayOfWeek): self
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (!in_array($type, [self::TYPE_WORK, self::TYPE_COLLECT], true)) {
            throw new InvalidArgumentException('Invalid type');
        }

        $this->type = $type;

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

    public function getLaboratory(): ?Laboratory
    {
        return $this->laboratory;
    }

    public function setLaboratory(?Laboratory $laboratory): self
    {
        $this->laboratory = $laboratory;

        return $this;
    }
}
