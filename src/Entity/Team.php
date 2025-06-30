<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "teams")]
#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[UniqueEntity(fields: ["name"])]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Team
{
    use TimestampableEntity;

    public const int LABORATORY_MANAGER_ID = 83;
    public const int COLLECTION_POINT_COORDINATOR_ID = 163;
    public const int REGION_MANAGER_ID = 82;
    public const int EDITOR_ID = 629;

    // Entities allowed for API and AMQP
    public const array ALLOWED_ENTITIES = [
        '8767d203-eaa4-5322-91a1-fa5ce408663f', // kierownik laboratorium
        'cee5e019-ac36-50c1-9966-b50a847e8157', // kierownik pracowni
        '3ba5e0fc-9bc1-5130-98dc-f347f8370feb', // koordynator punktu pobrań
        '33705304-8aa6-5c98-8ad6-a58a425245c4', // dyrektor regionalny
    ];

    #[ORM\OneToMany(targetEntity: "User", mappedBy: "team")]
    private Collection $users;

    #[ORM\ManyToOne(inversedBy: "teams")]
    #[ORM\JoinColumn]
    #[Gedmo\Versioned]
    private ?Role $role = null;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(["team"])]
    private ?int $id = null;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private bool $isActive;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(["team"])]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Groups(["team"])]
    #[Gedmo\Versioned]
    private ?string $description = null;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["team"])]
    private string $uuid;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->isActive = false;
        $this->uuid = Uuid::uuid4()->toString();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setTeam($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getTeam() === $this) {
                $user->setTeam();
            }
        }

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;

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
