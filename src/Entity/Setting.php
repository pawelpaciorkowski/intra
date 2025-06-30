<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SettingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "settings")]
#[ORM\Entity(repositoryClass: SettingRepository::class)]
#[UniqueEntity(fields: ["key"])]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Setting
{
    use TimestampableEntity;

    #[ORM\ManyToOne(inversedBy: "settings")]
    #[JoinColumn]
    #[Gedmo\Versioned]
    private ?Field $field = null;

    #[ORM\ManyToOne]
    #[JoinColumn]
    #[Gedmo\Versioned]
    private ?Section $section = null;

    #[ORM\ManyToOne]
    #[JoinColumn]
    #[Gedmo\Versioned]
    private ?Link $link = null;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private bool $isActive;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private bool $isHidden;

    #[ORM\Column(name: "setting_key", length: 50)]
    #[Assert\NotBlank]
    #[Assert\Regex("/^[\w]+$/")]
    #[Assert\Length(max: 50)]
    #[Gedmo\Versioned]
    private ?string $key = null;

    #[ORM\Column(name: "default_value", length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Gedmo\Versioned]
    private ?string $default = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    #[Gedmo\Versioned]
    private ?string $min_value = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    #[Gedmo\Versioned]
    private ?string $max_value = null;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private bool $isEmpty;

    #[ORM\Column(length: 100)]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ManyToMany(targetEntity: "Role")]
    #[JoinTable(name: "settings_to_roles", joinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ], inverseJoinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        )
    ])]
    #[ORM\OrderBy(["name" => "ASC"])]
    private Collection $roles;

    public function __construct()
    {
        $this->isActive = false;
        $this->isEmpty = false;
        $this->isHidden = false;
        $this->roles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function setDefault(?string $default): self
    {
        $this->default = $default;

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

    public function getField(): ?Field
    {
        return $this->field;
    }

    public function setField(?Field $field = null): self
    {
        $this->field = $field;

        return $this;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(?string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getMinValue(): ?string
    {
        return $this->min_value;
    }

    public function setMinValue(?string $minValue): self
    {
        $this->min_value = $minValue;

        return $this;
    }

    public function getMaxValue(): ?string
    {
        return $this->max_value;
    }

    public function setMaxValue(?string $maxValue): self
    {
        $this->max_value = $maxValue;

        return $this;
    }

    public function getIsEmpty(): bool
    {
        return $this->isEmpty;
    }

    public function setIsEmpty(bool $isEmpty): self
    {
        $this->isEmpty = $isEmpty;

        return $this;
    }

    public function getIsHidden(): bool
    {
        return $this->isHidden;
    }

    public function setIsHidden(bool $isHidden): self
    {
        $this->isHidden = $isHidden;

        return $this;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(?Section $section = null): self
    {
        $this->section = $section;

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

    public function getLink(): ?Link
    {
        return $this->link;
    }

    public function setLink(?Link $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }

        return $this;
    }
}
