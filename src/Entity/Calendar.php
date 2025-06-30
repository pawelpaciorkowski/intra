<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "calendars")]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Calendar
{
    use TimestampableEntity;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id;

    #[ORM\ManyToOne(inversedBy: "calendars")]
    #[ORM\JoinColumn]
    #[Groups(["collectionPoint"])]
    #[Gedmo\Versioned]
    private ?CollectionPoint $collectionPoint = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    #[Groups(["chatbotizeCalendar"])]
    #[Gedmo\Versioned]
    private ?ChatbotizeCalendar $chatbotizeCalendar = null;

    #[ORM\Column(type: "guid", nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[Groups(["calendar"])]
    #[Gedmo\Versioned]
    private ?string $instanceId = null;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["period"])]
    private string $uuid;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstanceId(): ?string
    {
        return $this->instanceId;
    }

    public function setInstanceId(?string $instanceId): self
    {
        $this->instanceId = $instanceId;

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

    public function getChatbotizeCalendar(): ?ChatbotizeCalendar
    {
        return $this->chatbotizeCalendar;
    }

    public function setChatbotizeCalendar(?ChatbotizeCalendar $chatbotizeCalendar): self
    {
        $this->chatbotizeCalendar = $chatbotizeCalendar;

        return $this;
    }
}
