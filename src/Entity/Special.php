<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SpecialRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "specials")]
#[ORM\Entity(repositoryClass: SpecialRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Special
{
    use TimestampableEntity;

    public const int LABORATORY_LIST = 1;
    public const int COLLECTION_POINT_LIST = 2;
    public const int COLLECTION_POINT_MAP = 3;
    public const int ISO = 4;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 512)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 512)]
    #[Gedmo\Versioned]
    private ?string $title = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $longText = null;

    #[ORM\ManyToOne(targetEntity: "SpecialTemplate", inversedBy: 'specials')]
    #[JoinColumn]
    private ?SpecialTemplate $specialTemplate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLongText(): ?string
    {
        return $this->longText;
    }

    public function setLongText(?string $longText): self
    {
        $this->longText = $longText;

        return $this;
    }

    public function getSpecialTemplate(): ?SpecialTemplate
    {
        return $this->specialTemplate;
    }

    public function setSpecialTemplate(?SpecialTemplate $specialTemplate): self
    {
        $this->specialTemplate = $specialTemplate;

        return $this;
    }
}
