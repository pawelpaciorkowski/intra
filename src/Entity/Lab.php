<?php


declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "labs")]
#[ORM\Entity]
#[UniqueEntity(fields: ["symbol"])]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Lab
{
    use TimestampableEntity;

    #[ORM\ManyToOne(inversedBy: "labs")]
    #[ORM\JoinColumn]
    #[Groups(["user"])]
    #[Gedmo\Versioned]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: "labs")]
    #[ORM\JoinColumn]
    #[Groups(["laboratory"])]
    #[Gedmo\Versioned]
    private ?Laboratory $laboratory = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    #[Groups(["labGroup"])]
    #[Gedmo\Versioned]
    private ?LabGroup $labGroup = null;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(["lab"])]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(["lab"])]
    #[Gedmo\Versioned]
    private ?string $symbol = null;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["lab"])]
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

    public function getLaboratory(): ?Laboratory
    {
        return $this->laboratory;
    }

    public function setLaboratory(?Laboratory $laboratory): self
    {
        $this->laboratory = $laboratory;

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

    public function getLabGroup(): ?LabGroup
    {
        return $this->labGroup;
    }

    public function setLabGroup(?LabGroup $labGroup): self
    {
        $this->labGroup = $labGroup;

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
}
