<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CategoryTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "category_templates")]
#[ORM\Entity(repositoryClass: CategoryTemplateRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class CategoryTemplate
{
    use TimestampableEntity;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 1024)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 1024)]
    #[Gedmo\Versioned]
    private string $name;

    #[ORM\Column(type: "text")]
    #[Gedmo\Versioned]
    private string $template;

    #[ORM\OneToMany(targetEntity: "Category", mappedBy: "categoryTemplate")]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function addCategory(self $category): self
    {
        $this->categories[] = $category;

        return $this;
    }

    public function removeCategory(self $category): void
    {
        $this->categories->removeElement($category);
    }

    public function getCategories(): ?Collection
    {
        return $this->categories;
    }
}
