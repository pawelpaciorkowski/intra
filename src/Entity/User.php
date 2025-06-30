<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\File;
use App\Repository\UserRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

use function serialize;
use function sprintf;
use function unserialize;

#[ORM\Table(name: "users")]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ["email"], groups: ["user_crud", "profile"])]
#[UniqueEntity(fields: ["username"], groups: ["user_crud", "profile"])]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, Serializable
{
    use File;
    use TimestampableEntity;

    public const string FILE_UPLOAD_DIR = 'avatar';
    public const int MAX_PASSWORD_AGE = 31536000; // 1 year

    #[ORM\OneToMany(targetEntity: "UserSetting", mappedBy: "user")]
    private Collection $userSettings;

    #[ORM\OneToMany(targetEntity: "Laboratory", mappedBy: "user")]
    private Collection $laboratories;

    #[ORM\OneToMany(targetEntity: "Lab", mappedBy: "user")]
    private Collection $labs;

    #[ORM\OneToMany(targetEntity: "CollectionPoint", mappedBy: "user")]
    private Collection $collectionPoints;

    #[ORM\OneToMany(targetEntity: "CollectionPoint", mappedBy: "user2")]
    private Collection $collectionPoints2;

    #[ORM\ManyToOne(inversedBy: "users")]
    #[JoinColumn]
    #[Assert\NotBlank(groups: ["user_crud"])]
    #[Groups(["team"])]
    #[Gedmo\Versioned]
    private ?Team $team = null;

    #[ORM\ManyToOne]
    #[JoinColumn]
    #[Groups(["position"])]
    #[Gedmo\Versioned]
    private ?Position $position = null;

    #[SecurityAssert\UserPassword(groups: ["profile_password"])]
    #[Assert\NotBlank(groups: ["profile_password"])]
    private ?string $oldPassword = null;

    #[Assert\NotBlank(groups: ["profile_password"])]
    #[Assert\Length(min: 12, max: 256, groups: ["profile_password"])]
    private ?string $newPassword = null;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(["user"])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100, groups: ["user_crud"])]
    #[Groups(["user"])]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    #[Assert\Length(max: 100, groups: ["user_crud"])]
    #[Groups(["user"])]
    #[Gedmo\Versioned]
    private ?string $surname = null;

    #[ORM\Column(length: 100, unique: true, nullable: true)]
    #[Assert\NotBlank(groups: ["user_crud", "profile"])]
    #[Assert\Length(min: 6, max: 32, groups: ["user_crud", "profile"])]
    #[Groups(["user"])]
    #[Gedmo\Versioned]
    private ?string $username = null;

    #[ORM\Column(length: 256, nullable: true)]
    #[Assert\NotBlank(groups: ["create_password", "recover_password"])]
    #[Assert\Length(min: 12, max: 256, groups: ["edit_password", "recover_password"])]
    private ?string $password = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?DateTimeInterface $passwordChangeAt = null;

    #[ORM\Column(length: 250)]
    #[Assert\NotBlank(groups: ["user_crud"])]
    #[Assert\Email(groups: ["user_crud"])]
    #[Assert\Length(max: 250)]
    #[Groups(["user"])]
    #[Gedmo\Versioned]
    private ?string $email = null;

    #[ORM\OneToMany(targetEntity: "Phone", mappedBy: "user", cascade: ["persist"], orphanRemoval: true)]
    #[Assert\Valid]
    #[Groups(["phone"])]
    private Collection $phones;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private bool $isActive;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private bool $isPasswordChangeRequired;

    #[ManyToMany(targetEntity: "Role")]
    #[JoinTable(name: "user_to_roles", joinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ], inverseJoinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ])]
    private Collection $additionalRoles;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $lastLoginAt = null;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["user"])]
    private string $uuid;

    #[ManyToMany(targetEntity: "Category", inversedBy: "users")]
    #[JoinTable(name: "users_to_categories", joinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ], inverseJoinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ])]
    private Collection $categories;

    public function __construct()
    {
        $this->isActive = false;
        $this->isPasswordChangeRequired = true;
        $this->laboratories = new ArrayCollection();
        $this->labs = new ArrayCollection();
        $this->collectionPoints = new ArrayCollection();
        $this->passwordChangeAt = new DateTime();
        $this->additionalRoles = new ArrayCollection();
        $this->userSettings = new ArrayCollection();
        $this->uuid = Uuid::uuid4()->toString();
        $this->collectionPoints2 = new ArrayCollection();
        $this->phones = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getRoles(): array
    {
        $userRoles = [];

        foreach ($this->additionalRoles as $role) {
            $userRoles[] = $role->getName();
        }

        if ($this->team instanceof Team) {
            $userRoles[] = $this->team->getRole()->getName();
        }

        return $userRoles;
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

    public function eraseCredentials(): void
    {
    }

    public function serialize(): string
    {
        return serialize(
            [
                $this->id,
                $this->username,
                $this->password,
                $this->isActive,
            ]
        );
    }

    public function unserialize($serialized): void
    {
        [$this->id, $this->username, $this->password, $this->isActive] = unserialize(
            $serialized,
            ['allowed_classes' => false]
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setLogin(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        if (!empty($password)) {
            $this->password = $password;
        }

        return $this;
    }

    public function clearPassword(): self
    {
        $this->password = null;

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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

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

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team = null): self
    {
        $this->team = $team;

        return $this;
    }

    public function getLastLoginAt(): ?DateTimeInterface
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?DateTimeInterface $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(?string $oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(?string $newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname($surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getFullname(): ?string
    {
        return sprintf('%s %s', $this->name, $this->surname);
    }

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(?Position $position = null): self
    {
        $this->position = $position;

        return $this;
    }

    public function getLaboratories(): Collection
    {
        return $this->laboratories;
    }

    public function addLaboratory(Laboratory $laboratory): self
    {
        if (!$this->laboratories->contains($laboratory)) {
            $this->laboratories[] = $laboratory;
            $laboratory->setUser($this);
        }

        return $this;
    }

    public function removeLaboratory(Laboratory $laboratory): self
    {
        if ($this->laboratories->contains($laboratory)) {
            $this->laboratories->removeElement($laboratory);
            // set the owning side to null (unless already changed)
            if ($laboratory->getUser() === $this) {
                $laboratory->setUser(null);
            }
        }

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
            $lab->setUser($this);
        }

        return $this;
    }

    public function removeLab(Lab $lab): self
    {
        if ($this->labs->contains($lab)) {
            $this->labs->removeElement($lab);
            // set the owning side to null (unless already changed)
            if ($lab->getUser() === $this) {
                $lab->setUser(null);
            }
        }

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
            $collectionPoint->setUser($this);
        }

        return $this;
    }

    public function removeCollectionPoint(CollectionPoint $collectionPoint): self
    {
        if ($this->collectionPoints->contains($collectionPoint)) {
            $this->collectionPoints->removeElement($collectionPoint);
            // set the owning side to null (unless already changed)
            if ($collectionPoint->getUser() === $this) {
                $collectionPoint->setUser(null);
            }
        }

        return $this;
    }

    public function getPasswordChangeAt(): ?DateTimeInterface
    {
        return $this->passwordChangeAt;
    }

    public function setPasswordChangeAt(?DateTimeInterface $passwordChangeAt): self
    {
        $this->passwordChangeAt = $passwordChangeAt;

        return $this;
    }

    public function getAdditionalRoles(): Collection
    {
        return $this->additionalRoles;
    }

    public function addAdditionalRole(Role $additionalRole): self
    {
        if (!$this->additionalRoles->contains($additionalRole)) {
            $this->additionalRoles[] = $additionalRole;
        }

        return $this;
    }

    public function removeAdditionalRole(Role $additionalRole): self
    {
        if ($this->additionalRoles->contains($additionalRole)) {
            $this->additionalRoles->removeElement($additionalRole);
        }

        return $this;
    }

    public function getUserSettings(): Collection
    {
        return $this->userSettings;
    }

    public function addUserSetting(UserSetting $userSetting): self
    {
        if (!$this->userSettings->contains($userSetting)) {
            $this->userSettings[] = $userSetting;
            $userSetting->setUser($this);
        }

        return $this;
    }

    public function removeUserSetting(UserSetting $userSetting): self
    {
        if ($this->userSettings->contains($userSetting)) {
            $this->userSettings->removeElement($userSetting);
            // set the owning side to null (unless already changed)
            if ($userSetting->getUser() === $this) {
                $userSetting->setUser(null);
            }
        }

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

    public function getIsPasswordChangeRequired(): ?bool
    {
        return $this->isPasswordChangeRequired;
    }

    public function setIsPasswordChangeRequired(bool $isPasswordChangeRequired): self
    {
        $this->isPasswordChangeRequired = $isPasswordChangeRequired;

        return $this;
    }

    public function getCollectionPoints2(): Collection
    {
        return $this->collectionPoints2;
    }

    public function addCollectionPoints2(CollectionPoint $collectionPoints2): self
    {
        if (!$this->collectionPoints2->contains($collectionPoints2)) {
            $this->collectionPoints2[] = $collectionPoints2;
            $collectionPoints2->setUser2($this);
        }

        return $this;
    }

    public function removeCollectionPoints2(CollectionPoint $collectionPoints2): self
    {
        if ($this->collectionPoints2->removeElement($collectionPoints2)) {
            // set the owning side to null (unless already changed)
            if ($collectionPoints2->getUser2() === $this) {
                $collectionPoints2->setUser2(null);
            }
        }

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
            $phone->setUser($this);
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        if ($this->phones->removeElement($phone)) {
            // set the owning side to null (unless already changed)
            if ($phone->getUser() === $this) {
                $phone->setUser(null);
            }
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function addCategory(Category $category): self
    {
        $this->categories[] = $category;

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getCategories(): ?Collection
    {
        return $this->categories;
    }
}
