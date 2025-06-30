<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PageCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "page_categories")]
#[ORM\Entity(repositoryClass: PageCategoryRepository::class)]
#[ORM\Index(name: "lft_idx", columns: ["lft"])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class PageCategory implements IndexInterface
{
    use TimestampableEntity;

    #[ORM\ManyToOne(inversedBy: "children")]
    #[JoinColumn(onDelete: "CASCADE")]
    private ?PageCategory $parent = null;

    #[ORM\OneToMany(targetEntity: "PageCategory", mappedBy: "parent")]
    private Collection $children;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[Gedmo\Versioned]
    #[ORM\Column]
    private bool $isActive;

    #[ORM\Column(type: 'smallint')]
    private int $depth;

    #[ORM\Column(type: 'smallint')]
    private int $lft;

    #[ORM\Column(type: 'smallint')]
    private int $rgt;

    #[ORM\Column(length: 256)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 256)]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: "PageFile", mappedBy: "pageCategory", orphanRemoval: true)]
    #[ORM\OrderBy(["sort" => "ASC"])]
    private Collection $pageFiles;

    #[ORM\ManyToOne(targetEntity: "Page", inversedBy: 'pageCategories')]
    #[JoinColumn]
    private ?Page $page = null;

    public function __construct()
    {
        $this->depth = 0;
        $this->lft = 0;
        $this->rgt = 0;
        $this->isActive = false;
        $this->children = new ArrayCollection();
        $this->pageFiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent = null): self
    {
        $this->parent = $parent;

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

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function setDepth(int $depth): self
    {
        $this->depth = $depth;

        return $this;
    }

    public function getLft(): int
    {
        return $this->lft;
    }

    public function setLft(int $lft): self
    {
        $this->lft = $lft;

        return $this;
    }

    public function getRgt(): int
    {
        return $this->rgt;
    }

    public function setRgt(int $rgt): self
    {
        $this->rgt = $rgt;

        return $this;
    }

    public function addChild(self $child): self
    {
        $this->children[] = $child;

        return $this;
    }

    public function removeChild(self $child): void
    {
        $this->children->removeElement($child);
    }

    public function getChildren(): ?Collection
    {
        return $this->children;
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

    public function addPageFile(PageFile $pageFile): self
    {
        $this->pageFiles[] = $pageFile;

        return $this;
    }

    public function removePageFile(PageFile $pageFile): void
    {
        $this->pageFiles->removeElement($pageFile);
    }

    public function getPageFiles(): ?Collection
    {
        return $this->pageFiles;
    }

    public function getFiles(): ?Collection
    {
        return $this->getPageFiles();
    }

    public function getIsIndex(): bool
    {
        return $this->isActive;
    }

    public function getDataForIndex(): array
    {
        return array_filter([
            'title' => $this->name,
            'description' => $this->description ? strip_tags($this->description) : null,
        ], static function ($e) {return !empty($e);});
    }

    public function getNameForIndex(): string
    {
        return $this->name;
    }

    public function getDescriptionForIndex(): ?string
    {
        if (!$this->description) {
            return null;
        }

        $text = strip_tags($this->description);

        if (strlen($text) > 512) {
            $text = substr($text, 0, 512) . '...';
        }

        return $text;
    }

    public function getPriority(): int
    {
        return 20;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page = null): self
    {
        $this->page = $page;

        return $this;
    }
}
