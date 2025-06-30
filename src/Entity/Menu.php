<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "menus")]
#[ORM\Entity(repositoryClass: MenuRepository::class)]
#[ORM\Index(name: "lft_idx", columns: ["lft"])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Menu
{
    use TimestampableEntity;

    #[ORM\ManyToOne(inversedBy: "children")]
    #[JoinColumn(onDelete: "CASCADE")]
    private ?Menu $parent = null;

    #[ORM\ManyToOne]
    #[JoinColumn]
    private ?Icon $icon = null;

    #[ORM\ManyToOne]
    #[JoinColumn]
    private ?Icon $additionalIcon = null;

    #[ORM\ManyToOne(inversedBy: "menus")]
    #[JoinColumn]
    private ?Link $link = null;

    #[ORM\OneToMany(targetEntity: "Menu", mappedBy: "parent")]
    private Collection $children;

    #[ManyToMany(targetEntity: "Link")]
    #[JoinTable(name: "menus_to_links", joinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ], inverseJoinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ])]
    private Collection $highlights;

    #[ManyToMany(targetEntity: "Role")]
    #[JoinTable(name: "menus_to_roles", joinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ], inverseJoinColumns: [
        new JoinColumn(
            onDelete: "CASCADE"
        ),
    ])]
    private Collection $roles;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private bool $isActive;

    #[ORM\Column(type: "smallint")]
    private int $depth;

    #[ORM\Column(type: "smallint")]
    private int $lft;

    #[ORM\Column(type: "smallint")]
    private int $rgt;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Gedmo\Versioned]
    private ?string $name = null;

    public function __construct()
    {
        $this->depth = 0;
        $this->lft = 0;
        $this->rgt = 0;
        $this->isActive = false;
        $this->children = new ArrayCollection();
        $this->highlights = new ArrayCollection();
        $this->roles = new ArrayCollection();
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

    public function addRole(Role $role): self
    {
        $this->roles[] = $role;

        return $this;
    }

    public function removeRole(Role $role): void
    {
        $this->roles->removeElement($role);
    }

    public function getRoles(): ?Collection
    {
        return $this->roles;
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
}
