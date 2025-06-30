<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LinkRepository;
use App\Validator\Constraints as AlabConstraints;
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

#[ORM\Table(name: "links")]
#[ORM\Entity(repositoryClass: LinkRepository::class)]
#[ORM\Index(name: "name_idx", columns: ["name"])]
#[UniqueEntity(fields: ["name"])]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Link
{
    use TimestampableEntity;

    #[ORM\OneToMany(targetEntity: "Menu", mappedBy: "link")]
    private Collection $menus;

    #[ORM\OneToMany(targetEntity: "Category", mappedBy: "link")]
    private Collection $categories;

    #[ManyToMany(targetEntity: "Role")]
    #[JoinTable(name: "links_to_roles", joinColumns: [
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

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[AlabConstraints\RouteExists]
    #[Gedmo\Versioned]
    private ?string $name = null;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->roles = new ArrayCollection();
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

    public function addMenu(Menu $menu): self
    {
        $this->menus[] = $menu;

        return $this;
    }

    public function removeMenu(Menu $menu): void
    {
        $this->menus->removeElement($menu);
    }

    public function getMenus(): ?Collection
    {
        return $this->menus;
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

    public function addCategory(Category $category): self
    {
        $this->categorys[] = $category;

        return $this;
    }

    public function removeCategory(Category $category): void
    {
        $this->categories->removeElement($category);
    }

    public function getCategories(): ?Collection
    {
        return $this->categories;
    }
}
