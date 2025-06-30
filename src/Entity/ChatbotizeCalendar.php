<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Table(name: "chatbotize_calendars")]
#[ORM\Entity]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class ChatbotizeCalendar
{
    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Groups(["chatbotizeCalendar"])]
    private ?string $name = null;

    #[ORM\Column(length: 6)]
    #[Gedmo\Versioned]
    private ?string $color = null;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["chatbotizeCalendar"])]
    private ?string $uuid = null;

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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }
}
