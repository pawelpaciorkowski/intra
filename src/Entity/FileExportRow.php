<?php


declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "file_export_rows")]
#[ORM\Entity]
class FileExportRow
{
    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $key = null;

    #[ORM\ManyToOne(inversedBy: "fileExportRows")]
    #[ORM\JoinColumn]
    private ?FileExport $fileExport = null;

    #[ORM\Column(name: 'sequence', type: "smallint")]
    private int $order;

    #[ORM\OneToMany(targetEntity: "UserFileExportRow", mappedBy: "fileExportRow")]
    private Collection $userFileExportRows;

    #[ORM\Column]
    private bool $isInclude;

    public function __construct()
    {
        $this->userFileExportRows = new ArrayCollection();
        $this->order = 0;
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

    public function getFileExport(): ?FileExport
    {
        return $this->fileExport;
    }

    public function setFileExport(?FileExport $fileExport): self
    {
        $this->fileExport = $fileExport;

        return $this;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

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

    public function getUserFileExportRows(): Collection
    {
        return $this->userFileExportRows;
    }

    public function addUserFileExportRow(UserFileExportRow $userFileExportRow): self
    {
        if (!$this->userFileExportRows->contains($userFileExportRow)) {
            $this->userFileExportRows[] = $userFileExportRow;
            $userFileExportRow->setFileExportRow($this);
        }

        return $this;
    }

    public function removeUserFileExportRow(UserFileExportRow $userFileExportRow): self
    {
        if ($this->userFileExportRows->removeElement($userFileExportRow)) {
            // set the owning side to null (unless already changed)
            if ($userFileExportRow->getFileExportRow() === $this) {
                $userFileExportRow->setFileExportRow(null);
            }
        }

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
