<?php


declare(strict_types=1);

namespace App\Entity;

use App\Repository\FieldRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "fields")]
#[ORM\Entity(repositoryClass: FieldRepository::class)]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Field
{
    use TimestampableEntity;

    #[ORM\OneToMany(targetEntity: "Setting", mappedBy: "field")]
    private Collection $settings;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Gedmo\Versioned]
    private ?string $type = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Gedmo\Versioned]
    private ?string $langType = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    #[Gedmo\Versioned]
    private ?string $className = null;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private bool $isActive;

    public function __construct()
    {
        $this->settings = new ArrayCollection();
        $this->isActive = false;
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

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function addSetting(Setting $settings): self
    {
        $this->settings[] = $settings;

        return $this;
    }

    public function removeSetting(Setting $settings): void
    {
        $this->settings->removeElement($settings);
    }

    public function getSettings(): Collection
    {
        return $this->settings;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function setClassName(?string $className): self
    {
        $this->className = $className;

        return $this;
    }

    public function getLangType(): ?string
    {
        return $this->langType;
    }

    public function setLangType(?string $langType): self
    {
        $this->langType = $langType;

        return $this;
    }
}
