<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserSettingRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "user_settings")]
#[ORM\Entity(repositoryClass: UserSettingRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class UserSetting
{
    use TimestampableEntity;

    #[ORM\ManyToOne(inversedBy: "userSettings")]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[Gedmo\Versioned]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[Gedmo\Versioned]
    private ?Setting $setting = null;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Gedmo\Versioned]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

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

    public function getSetting(): ?Setting
    {
        return $this->setting;
    }

    public function setSetting(?Setting $setting): self
    {
        $this->setting = $setting;

        return $this;
    }
}
