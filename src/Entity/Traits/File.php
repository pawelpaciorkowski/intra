<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use App\Entity\LogDbTracking;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

use function bin2hex;
use function random_bytes;
use function unlink;

#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
trait File
{
    private ?string $temp = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Gedmo\Versioned]
    private ?string $temporaryFilename = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Gedmo\Versioned]
    private ?string $originalFilename = null;

    private ?UploadedFile $file = null;

    public function getWebPath(): ?string
    {
        return null === $this->temporaryFilename ? null : $this->getUploadDir() . '/' . $this->temporaryFilename;
    }

    protected function getUploadDir(): string
    {
        return 'uploads/' . $this::FILE_UPLOAD_DIR;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function preUpload(): void
    {
        if (null !== $this->getFile()) {
            $filename = bin2hex(random_bytes(20));
            $this->temporaryFilename = $filename . '.' . $this->getFile()->guessExtension();
            $this->originalFilename = $this->getFile()->getClientOriginalName();
        }
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file = null): void
    {
        $this->file = $file;
        if (null !== $this->temporaryFilename) {
            $this->temp = $this->temporaryFilename;
            $this->temporaryFilename = null;
        } else {
            $this->temporaryFilename = 'initial';
        }
    }

    #[ORM\PostPersist]
    #[ORM\PostUpdate]
    public function upload(): void
    {
        if (null === $this->getFile()) {
            return;
        }

        $this->getFile()->move($this->getUploadRootDir(), $this->temporaryFilename);

        if (null !== $this->temp) {
            unlink($this->getUploadRootDir() . '/' . $this->temp);
            $this->temp = null;
        }
        $this->file = null;
    }

    protected function getUploadRootDir(): string
    {
        return __DIR__ . '/../../../' . $this->getUploadDir();
    }

    #[ORM\PostRemove]
    public function removeUpload(): void
    {
        $file = $this->getAbsolutePath();
        if ($file) {
            unlink($file);
        }
    }

    public function getAbsolutePath(): ?string
    {
        return null === $this->temporaryFilename ? null : $this->getUploadRootDir() . '/' . $this->temporaryFilename;
    }

    public function getPath(): ?string
    {
        return $this->temporaryFilename;
    }

    public function setPath(?string $path): self
    {
        $this->temporaryFilename = $path;

        return $this;
    }

    public function getTemporaryFilename(): ?string
    {
        return $this->temporaryFilename;
    }

    public function setTemporaryFilename(?string $temporaryFilename): void
    {
        $this->temporaryFilename = $temporaryFilename;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(?string $originalFilename): void
    {
        $this->originalFilename = $originalFilename;
    }
}
