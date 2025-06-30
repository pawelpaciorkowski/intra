<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "collection_point_extra_dates")]
#[ORM\Entity]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class CollectionPointExtraDate implements MessageSerializerInterface, CollectionPointDateInterface
{
    use TimestampableEntity;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: "collectionPointExtraDates")]
    #[ORM\JoinColumn]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?CollectionPoint $collectionPoint;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank]
    #[Groups(["collectionPointExtraDate"])]
    #[Gedmo\Versioned]
    private ?DateTimeInterface $startAt = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank]
    #[Assert\Expression("this.getStartAt() <: this.getEndAt()", message: "Data końcowa nie może być wcześniejsza od daty początkowej")]
    #[Groups(["collectionPointExtraDate"])]
    #[Gedmo\Versioned]
    private ?DateTimeInterface $endAt;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Groups(["collectionPointExtraDate"])]
    private ?string $comment = null;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["collectionPointExtraDate"])]
    private string $uuid;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
    }

    public static function getSerializedGroups(): array
    {
        return [
            'collectionPointExtraDate',
            'collectionPoint',
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartAt(): ?DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

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

    public function getCollectionPoint(): ?CollectionPoint
    {
        return $this->collectionPoint;
    }

    public function setCollectionPoint(?CollectionPoint $collectionPoint): self
    {
        $this->collectionPoint = $collectionPoint;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
