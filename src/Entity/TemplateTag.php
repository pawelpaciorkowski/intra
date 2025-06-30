<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TemplateTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "template_tags")]
#[ORM\Entity(repositoryClass: TemplateTagRepository::class)]
#[ORM\Index(name: "tag_idx", columns: ["tag"])]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class TemplateTag
{
    use TimestampableEntity;

    #[ORM\OneToMany(targetEntity: "Template", mappedBy: "templateTag")]
    private Collection $templates;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Gedmo\Versioned]
    private ?string $tag = null;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private bool $isActive;

    public function __construct()
    {
        $this->templates = new ArrayCollection();
        $this->isActive = false;
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

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(?string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getTemplates(): Collection
    {
        return $this->templates;
    }

    public function addTemplate(Template $template): self
    {
        if (!$this->templates->contains($template)) {
            $this->templates[] = $template;
            $template->setTemplateTag($this);
        }

        return $this;
    }

    public function removeTemplate(Template $template): self
    {
        if ($this->templates->contains($template)) {
            $this->templates->removeElement($template);
            // set the owning side to null (unless already changed)
            if ($template->getTemplateTag() === $this) {
                $template->setTemplateTag(null);
            }
        }

        return $this;
    }
}
