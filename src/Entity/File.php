<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "files")]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class File
{
    use Traits\File;
    use TimestampableEntity;

    public const FILE_UPLOAD_DIR = 'page';

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 512)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 512)]
    #[Gedmo\Versioned]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: "files")]
    #[ORM\JoinColumn]
    private ?FileSection $fileSection = null;

    #[ORM\Column(name: 'sort')]
    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    private int $order;

    public function __construct()
    {
        $this->order = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFileSection(): ?FileSection
    {
        return $this->fileSection;
    }

    public function setFileSection(?FileSection $fileSection = null): self
    {
        $this->fileSection = $fileSection;

        return $this;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(int $order): self
    {
        $this->order = $order;

        return $this;
    }
}
