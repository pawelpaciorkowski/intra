<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "categories")]
#[ORM\Index(name: "lft_idx", columns: ["lft"])]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Category implements IndexInterface
{
    use TimestampableEntity;

    #[ORM\ManyToOne(inversedBy: "children")]
    #[JoinColumn(onDelete: "CASCADE")]
    private ?Category $parent = null;

    #[ORM\ManyToOne]
    #[JoinColumn]
    private ?Icon $icon = null;

    #[ORM\ManyToOne]
    #[JoinColumn]
    private ?Icon $additionalIcon = null;

    #[ORM\ManyToOne(inversedBy: "categories")]
    #[JoinColumn]
    private ?Link $link = null;

    #[ORM\ManyToOne]
    #[JoinColumn]
    private ?Page $page = null;

    #[ORM\OneToMany(targetEntity: "Category", mappedBy: "parent")]
    private Collection $children;

    #[ManyToMany(targetEntity: "Link")]
    #[JoinTable(name: "categories_to_links", joinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ], inverseJoinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ])]
    private Collection $highlights;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[Gedmo\Versioned]
    #[ORM\Column]
    private bool $isActive;

    #[Gedmo\Versioned]
    #[ORM\Column]
    private bool $isShowChildren;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private bool $isShowCategories;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private bool $isShowPagesInSubcategories;

    #[ORM\Column(type: 'smallint')]
    private int $depth;

    #[ORM\Column(type: 'smallint')]
    private int $lft;

    #[ORM\Column(type: 'smallint')]
    private int $rgt;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Gedmo\Versioned]
    private ?string $url = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $description = null;

    #[ManyToMany(targetEntity: "Page", mappedBy: "categories")]
    private Collection $pages;

    #[ManyToMany(targetEntity: "User", mappedBy: "categories")]
    private Collection $users;

    #[ORM\ManyToOne(targetEntity: "CategoryTemplate", inversedBy: 'categories')]
    #[JoinColumn]
    private ?CategoryTemplate $categoryTemplate = null;

    public function __construct()
    {
        $this->depth = 0;
        $this->lft = 0;
        $this->rgt = 0;
        $this->isActive = false;
        $this->children = new ArrayCollection();
        $this->highlights = new ArrayCollection();
        $this->isShowCategories = false;
        $this->isShowPagesInSubcategories = false;
        $this->pages = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->isShowChildren = true;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIcon(): ?Icon
    {
        return $this->icon;
    }

    public function setIcon(?Icon $icon = null): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getLink(): ?Link
    {
        return $this->link;
    }

    public function setLink(?Link $link = null): self
    {
        $this->link = $link;

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

    public function addHighlight(Link $highlight): self
    {
        $this->highlights[] = $highlight;

        return $this;
    }

    public function removeHighlight(Link $highlight): void
    {
        $this->highlights->removeElement($highlight);
    }

    public function getHighlights(): ?Collection
    {
        return $this->highlights;
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

    public function getAdditionalIcon(): ?Icon
    {
        return $this->additionalIcon;
    }

    public function setAdditionalIcon(?Icon $additionalIcon): self
    {
        $this->additionalIcon = $additionalIcon;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIsShowCategories(): bool
    {
        return $this->isShowCategories;
    }

    public function setIsShowCategories(bool $isShowCategories): self
    {
        $this->isShowCategories = $isShowCategories;

        return $this;
    }

    public function getIsShowPagesInSubcategories(): bool
    {
        return $this->isShowPagesInSubcategories;
    }

    public function setIsShowPagesInSubcategories(bool $isShowPagesInSubcategories): self
    {
        $this->isShowPagesInSubcategories = $isShowPagesInSubcategories;

        return $this;
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

    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): self
    {
        if (!$this->pages->contains($page)) {
            $this->pages[] = $page;
            $page->addCategory($this);
        }

        return $this;
    }

    public function removePage(Page $page): self
    {
        if ($this->pages->removeElement($page)) {
            $page->removeCategory($this);
        }

        return $this;
    }

    public function getDataForIndex(): array
    {
        return array_filter([
            'name' => $this->name,
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

    public function getIsIndex(): bool
    {
        return $this->isActive;
    }

    public function getCategoryTemplate(): ?CategoryTemplate
    {
        return $this->categoryTemplate;
    }

    public function setCategoryTemplate(?CategoryTemplate $categoryTemplate): self
    {
        $this->categoryTemplate = $categoryTemplate;

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
            $user->addCategory($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeCategory($this);
        }

        return $this;
    }

    public function isShowChildren(): bool
    {
        return $this->isShowChildren;
    }

    public function setIsShowChildren(bool $isShowChildren): self
    {
        $this->isShowChildren = $isShowChildren;

        return $this;
    }
}
