<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SpecialTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "special_templates")]
#[ORM\Entity(repositoryClass: SpecialTemplateRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class SpecialTemplate
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

    #[ORM\OneToMany(targetEntity: "Special", mappedBy: "specialTemplate")]
    private Collection $specials;

    public function __construct()
    {
        $this->specials = new ArrayCollection();
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

    public function addSpecial(self $special): self
    {
        $this->specials[] = $special;

        return $this;
    }

    public function removeSpecial(self $special): void
    {
        $this->specials->removeElement($special);
    }

    public function getSpecials(): ?Collection
    {
        return $this->specials;
    }
}
