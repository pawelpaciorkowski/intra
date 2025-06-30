<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "departments")]
#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
class Department
{
    use TimestampableEntity;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: "children")]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?Department $parent = null;

    #[ORM\OneToMany(targetEntity: "Department", mappedBy: "parent")]
    private Collection $children;

    #[ORM\Column(type: "smallint")]
    private int $depth;

    #[ORM\Column(type: "smallint")]
    private int $lft;

    #[ORM\Column(type: "smallint")]
    private int $rgt;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(["department"])]
    private ?string $name = null;

    public function __construct()
    {
        $this->depth = 0;
        $this->lft = 0;
        $this->rgt = 0;
        $this->children = new ArrayCollection();
    }

    public static function create(string $name, int $depth = 0, int $lft = 0, int $rgt = 0): self
    {
        $department = new self();

        $department
            ->setName($name)
            ->setDepth($depth)
            ->setLft($lft)
            ->setRgt($rgt);

        return $department;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepth(): ?int
    {
        return $this->depth;
    }

    public function setDepth(int $depth): self
    {
        $this->depth = $depth;

        return $this;
    }

    public function getLft(): ?int
    {
        return $this->lft;
    }

    public function setLft(int $lft): self
    {
        $this->lft = $lft;

        return $this;
    }

    public function getRgt(): ?int
    {
        return $this->rgt;
    }

    public function setRgt(int $rgt): self
    {
        $this->rgt = $rgt;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Department $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Department $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent();
            }
        }

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
}
