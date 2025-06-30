<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PageTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "page_templates")]
#[ORM\Entity(repositoryClass: PageTemplateRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class PageTemplate
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

    #[ORM\OneToMany(targetEntity: "Page", mappedBy: "pageTemplate")]
    private Collection $pages;

    public function __construct()
    {
        $this->pages = new ArrayCollection();
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

    public function addPage(self $page): self
    {
        $this->pages[] = $page;

        return $this;
    }

    public function removePage(self $page): void
    {
        $this->pages->removeElement($page);
    }

    public function getPages(): ?Collection
    {
        return $this->pages;
    }
}
