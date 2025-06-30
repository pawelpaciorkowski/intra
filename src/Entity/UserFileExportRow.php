<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserFileExportRowRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Table(name: "user_file_export_rows")]
#[ORM\Entity(repositoryClass: UserFileExportRowRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class UserFileExportRow
{
    use TimestampableEntity;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[Gedmo\Versioned]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: "userFileExportRows")]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[Gedmo\Versioned]
    private ?FileExportRow $fileExportRow = null;

    #[ORM\Column(name: 'sequence', type: "smallint")]
    private ?int $order;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private bool $isInclude;

    public function __construct()
    {
        $this->isInclude = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFileExportRow(): ?FileExportRow
    {
        return $this->fileExportRow;
    }

    public function setFileExportRow(?FileExportRow $fileExportRow): self
    {
        $this->fileExportRow = $fileExportRow;

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

    public function getIsInclude(): ?bool
    {
        return $this->isInclude;
    }

    public function setIsInclude(bool $isInclude): self
    {
        $this->isInclude = $isInclude;

        return $this;
    }
}
