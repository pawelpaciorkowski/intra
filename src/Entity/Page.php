<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PageRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "pages")]
#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Page implements IndexInterface
{
    use TimestampableEntity;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private bool $isActive;

    #[ORM\Column(length: 512)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 512)]
    #[Gedmo\Versioned]
    private ?string $title = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    private ?string $shortText = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    private ?string $longText = null;

    #[ManyToMany(targetEntity: "Category", inversedBy: "pages")]
    #[JoinTable(name: "pages_to_categories", joinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ], inverseJoinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ])]
    private Collection $categories;

    #[ORM\OneToMany(targetEntity: "FileSection", mappedBy: "page", cascade: ["persist"], orphanRemoval: true)]
    #[Assert\Valid]
    #[OrderBy(["order" => "ASC"])]
    private Collection $fileSections;

    #[ORM\OneToMany(targetEntity: "PageCategory", mappedBy: "page", cascade: ["persist"], orphanRemoval: true)]
    #[Assert\Valid]
    #[OrderBy(["lft" => "ASC"])]
    private Collection $pageCategories;

    #[ORM\ManyToOne(targetEntity: "PageTemplate", inversedBy: 'pages')]
    #[JoinColumn]
    private ?PageTemplate $pageTemplate = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    private ?DateTimeInterface $publishedAt = null;

    public function __construct()
    {
        $this->isActive = true;
        $this->categories = new ArrayCollection();
        $this->fileSections = new ArrayCollection();
        $this->pageCategories = new ArrayCollection();
        $this->publishedAt = new DateTime();
    }

    public function __clone()
    {
        $this->id = null;
        $this->isActive = false;
        $this->title .= ' (kopia)';
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getLongText(): ?string
    {
        return $this->longText;
    }

    public function setLongText(?string $longText): self
    {
        $this->longText = $longText;

        return $this;
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

    public function getFileSections(): Collection
    {
        return $this->fileSections;
    }

    public function addFileSection(FileSection $fileSection): self
    {
        if (!$this->fileSections->contains($fileSection)) {
            $this->fileSections[] = $fileSection;
            $fileSection->setPage($this);
        }

        return $this;
    }

    public function removeFileSection(FileSection $fileSection): self
    {
        if ($this->fileSections->removeElement($fileSection)) {
            // set the owning side to null (unless already changed)
            if ($fileSection->getPage() === $this) {
                $fileSection->setPage();
            }
        }

        return $this;
    }

    public function getDataForIndex(): array
    {
        return array_filter([
            'title' => $this->title,
            'shortText' => $this->shortText ? strip_tags($this->shortText) : null,
            'longText' => $this->longText ? strip_tags($this->longText) : null,
        ], static function ($e) {return !empty($e);});
    }

    public function getNameForIndex(): string
    {
        return $this->title;
    }

    public function getDescriptionForIndex(): ?string
    {
        if (!$this->shortText) {
            return null;
        }

        $text = strip_tags($this->shortText);

        if (strlen($text) > 512) {
            $text = substr($text, 0, 512) . '...';
        }

        return $text;
    }

    public function getPriority(): int
    {
        return 10;
    }

    public function getIsIndex(): bool
    {
        return $this->isActive;
    }

    public function getPageTemplate(): ?PageTemplate
    {
        return $this->pageTemplate;
    }

    public function setPageTemplate(?PageTemplate $pageTemplate): self
    {
        $this->pageTemplate = $pageTemplate;

        return $this;
    }

    public function getPublishedAt(): ?DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getPageCategories(): Collection
    {
        return $this->pageCategories;
    }

    public function addPageCategory(PageCategory $pageCategory): self
    {
        if (!$this->pageCategories->contains($pageCategory)) {
            $this->pageCategories[] = $pageCategory;
            $pageCategory->setPage($this);
        }

        return $this;
    }

    public function removePageCategory(PageCategory $pageCategory): self
    {
        if ($this->pageCategories->removeElement($pageCategory)) {
            // set the owning side to null (unless already changed)
            if ($pageCategory->getPage() === $this) {
                $pageCategory->setPage();
            }
        }

        return $this;
    }
}
