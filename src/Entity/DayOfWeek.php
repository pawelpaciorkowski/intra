<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Table]
#[ORM\Entity]
#[ORM\Cache(usage: "READ_ONLY")]
class DayOfWeek
{
    public const int DAY_OF_WEEK_MONDAY_ID = 1;
    public const int DAY_OF_WEEK_TUESDAY_ID = 2;
    public const int DAY_OF_WEEK_WEDNESDAY_ID = 3;
    public const int DAY_OF_WEEK_THURSDAY_ID = 4;
    public const int DAY_OF_WEEK_FRIDAY_ID = 5;
    public const int DAY_OF_WEEK_SATURDAY_ID = 6;
    public const int DAY_OF_WEEK_SUNDAY_ID = 7;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 1024)]
    #[Groups(["dayOfWeek"])]
    private ?string $name = null;

    #[ORM\Column(length: 1024)]
    private ?string $shortName = null;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["dayOfWeek"])]
    private string $uuid;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }
}
