<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\TargetType;
use App\Entity\Traits\File;
use App\Repository\BoxRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "boxes")]
#[ORM\Entity(repositoryClass: BoxRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Box
{
    use File;
    use TimestampableEntity;

    public const string FILE_UPLOAD_DIR = 'box';

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 1024)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 1024)]
    #[Gedmo\Versioned]
    private string $title;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $shortText = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    private DateTimeInterface $date;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    private ?Link $link;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    private ?Category $category;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn]
    private ?Page $page;

    #[Gedmo\Versioned]
    #[ORM\Column]
    private bool $isActive;

    #[ORM\Column(length: 32, nullable: true, enumType: TargetType::class)]
    #[Gedmo\Versioned]
    private ?TargetType $targetType = null;

    #[ORM\Column(type: 'smallint')]
    private int $sort;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Gedmo\Versioned]
    private ?string $url = null;

    public function __construct()
    {
        $this->date = new DateTime();
        $this->sort = 0;
        $this->isActive = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): self
    {
        $this->page = $page;

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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function getShortText(): ?string
    {
        return $this->shortText;
    }

    public function setShortText(?string $shortText): self
    {
        $this->shortText = $shortText;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getTargetType(): ?TargetType
    {
        return $this->targetType;
    }

    public function setTargetType(?TargetType $targetType): self
    {
        $this->targetType = $targetType;

        return $this;
    }
}
