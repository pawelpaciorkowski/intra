<?php


declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "file_exports")]
#[ORM\Entity]
class FileExport
{
    public const int COLLECTION_POINT = 1;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $key = null;

    #[ORM\OneToMany(targetEntity: "FileExportRow", mappedBy: "fileExport")]
    #[ORM\JoinColumn(name: "file_export_id", referencedColumnName: "id")]
    private Collection $fileExportRows;

    public function __construct()
    {
        $this->fileExportRows = new ArrayCollection();
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

    public function getFileExportRows(): Collection
    {
        return $this->fileExportRows;
    }

    public function addFileExportRow(FileExportRow $fileExportRow): self
    {
        if (!$this->fileExportRows->contains($fileExportRow)) {
            $this->fileExportRows[] = $fileExportRow;
            $fileExportRow->setFileExport($this);
        }

        return $this;
    }

    public function removeFileExportRow(FileExportRow $fileExportRow): self
    {
        if ($this->fileExportRows->removeElement($fileExportRow)) {
            // set the owning side to null (unless already changed)
            if ($fileExportRow->getFileExport() === $this) {
                $fileExportRow->setFileExport(null);
            }
        }

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
}
