<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\FileType;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "page_file_file_history")]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class PageFileFileHistory
{
    use TimestampableEntity;

    public const string FILE_UPLOAD_DIR = 'page';

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Gedmo\Versioned]
    private ?string $filename = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Gedmo\Versioned]
    private ?string $temporaryFilename = null;

    #[ORM\Column(length: 32, nullable: true, enumType: FileType::class)]
    #[Gedmo\Versioned]
    private ?FileType $fileType = null;

    #[ORM\ManyToOne(inversedBy: "pageFileFileHistory")]
    #[JoinColumn(onDelete: 'CASCADE')]
    private ?PageFile $pageFile = null;

    #[ORM\ManyToOne]
    #[JoinColumn]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getTemporaryFilename(): ?string
    {
        return $this->temporaryFilename;
    }

    public function setTemporaryFilename(?string $temporaryFilename): self
    {
        $this->temporaryFilename = $temporaryFilename;

        return $this;
    }

    public function getFileType(): ?FileType
    {
        return $this->fileType;
    }

    public function setFileType(?FileType $fileType): self
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getPageFile(): ?PageFile
    {
        return $this->pageFile;
    }

    public function setPageFile(?PageFile $pageFile): self
    {
        $this->pageFile = $pageFile;

        return $this;
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

    protected function getUploadRootDir(): string
    {
        return __DIR__ . '/../../' . $this->getUploadDir();
    }

    protected function getUploadDir(): string
    {
        return 'uploads/' . $this::FILE_UPLOAD_DIR;
    }

    public function getTemporaryAbsolutePath(): ?string
    {
        return null === $this->temporaryFilename ? null : $this->getUploadRootDir().'/'.$this->temporaryFilename;
    }
}
