<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table]
#[ORM\Entity]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class CollectionPointPartner
{
    use TimestampableEntity;

    private const FILES = [
        'Logo',
        'Logo2',
    ];

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 128)]
    #[Assert\NotBlank]
    #[Groups(["collectionPointPartner"])]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(type: "guid", unique: true)]
    #[Groups(["collectionPointPartner"])]
    private string $uuid;

    private $fileLogoToDelete;

    #[Assert\File(maxSize: "128M")]
    private ?UploadedFile $fileLogo = null;

    #[Groups(["collectionPointPartnerFile"])]
    private ?string $fileLogoMime = null;

    #[Groups(["collectionPointPartnerFile"])]
    private ?string $fileLogoContent = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Gedmo\Versioned]
    private ?string $fileLogoTemporaryFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Gedmo\Versioned]
    private ?string $fileLogoOriginalFilename = null;

    private $fileLogo2ToDelete;

    #[Assert\File(maxSize: "128M")]
    private ?UploadedFile $fileLogo2 = null;

    #[Groups(["collectionPointPartnerFile"])]
    private ?string $fileLogo2Mime = null;

    #[Groups(["collectionPointPartnerFile"])]
    private ?string $fileLogo2Content = null;

    #[ORM\Column(name: 'file_logo2temporary_filename', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Gedmo\Versioned]
    private ?string $fileLogo2TemporaryFilename = null;

    #[ORM\Column(name: 'file_logo2original_filename', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Gedmo\Versioned]
    private ?string $fileLogo2OriginalFilename = null;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function preInvoke(): void
    {
        foreach (self::FILES as $file) {
            if (null !== $this->{'getFile' . $file}()) {
                $filename = bin2hex(random_bytes(20));
                $this->{'file' . $file . 'TemporaryFilename'} = $filename . '.' . $this->{'getFile' . $file}(
                )->guessExtension();
                $this->{'file' . $file . 'OriginalFilename'} = $this->{'getFile' . $file}()->getClientOriginalName();
            }
        }
    }

    #[ORM\PostPersist]
    #[ORM\PostUpdate]
    public function postInvoke(): void
    {
        foreach (self::FILES as $file) {
            if (null !== $this->{'getFile' . $file}()) {
                $this->{'getFile' . $file}()->move(
                    $this->getUploadRootDir(),
                    $this->{'file' . $file . 'TemporaryFilename'}
                );
                $this->{'file' . $file} = null;
            }

            if (null !== $this->{'file' . $file . 'ToDelete'}) {
                unlink($this->getUploadRootDir() . '/' . $this->{'file' . $file . 'ToDelete'});
                $this->{'file' . $file . 'ToDelete'} = null;
            }
        }
    }

    protected function getUploadRootDir(): string
    {
        return __DIR__ . '/../../' . $this->getUploadDir();
    }

    protected function getUploadDir(): string
    {
        return 'uploads/collection-point-partner';
    }

    #[ORM\PostRemove]
    public function removeUpload(): void
    {
        foreach (self::FILES as $file) {
            if (null !== $this->{'file' . $file . 'TemporaryFilename'}) {
                unlink($this->getUploadRootDir() . '/' . $this->{'file' . $file . 'TemporaryFilename'});
            }
        }
    }

    public function getFileLogo(): ?UploadedFile
    {
        return $this->fileLogo;
    }

    public function setFileLogo(?UploadedFile $file = null): self
    {
        $this->fileLogo = $file;
        if (null !== $this->fileLogoTemporaryFilename) {
            $this->fileLogoToDelete = $this->fileLogoTemporaryFilename;
            $this->fileLogoTemporaryFilename = null;
        } elseif (null !== $file) {
            $this->fileLogoTemporaryFilename = 'initial';
        }

        return $this;
    }

    public function getFileLogo2(): ?UploadedFile
    {
        return $this->fileLogo2;
    }

    public function setFileLogo2(?UploadedFile $file = null): self
    {
        $this->fileLogo2 = $file;
        if (null !== $this->fileLogo2TemporaryFilename) {
            $this->fileLogo2ToDelete = $this->fileLogo2TemporaryFilename;
            $this->fileLogo2TemporaryFilename = null;
        } elseif (null !== $file) {
            $this->fileLogo2TemporaryFilename = 'initial';
        }

        return $this;
    }

    public function getFileLogoTemporaryFilename(): ?string
    {
        return $this->fileLogoTemporaryFilename;
    }

    public function setFileLogoTemporaryFilename(?string $fileLogoTemporaryFilename): self
    {
        $this->fileLogoTemporaryFilename = $fileLogoTemporaryFilename;

        return $this;
    }

    public function getFileLogoOriginalFilename(): ?string
    {
        return $this->fileLogoOriginalFilename;
    }

    public function setFileLogoOriginalFilename(?string $fileLogoOriginalFilename): self
    {
        $this->fileLogoOriginalFilename = $fileLogoOriginalFilename;

        return $this;
    }

    public function getFileLogo2TemporaryFilename(): ?string
    {
        return $this->fileLogo2TemporaryFilename;
    }

    public function setFileLogo2TemporaryFilename(?string $fileLogo2TemporaryFilename): self
    {
        $this->fileLogo2TemporaryFilename = $fileLogo2TemporaryFilename;

        return $this;
    }

    public function getFileLogo2OriginalFilename(): ?string
    {
        return $this->fileLogo2OriginalFilename;
    }

    public function setFileLogo2OriginalFilename(?string $fileLogo2OriginalFilename): self
    {
        $this->fileLogo2OriginalFilename = $fileLogo2OriginalFilename;

        return $this;
    }

    public function getFileLogoMime(): ?string
    {
        if ($this->fileLogoTemporaryFilename) {
            $mime = mime_content_type($this->getUploadRootDir() . '/' . $this->fileLogoTemporaryFilename);

            if (false !== $mime) {
                return $mime;
            }
        }

        return null;
    }

    public function getFileLogoContent(): ?string
    {
        if ($this->fileLogoTemporaryFilename) {
            return base64_encode(file_get_contents($this->getUploadRootDir() . '/' . $this->fileLogoTemporaryFilename));
        }

        return null;
    }

    public function getFileLogo2Mime(): ?string
    {
        if ($this->fileLogo2TemporaryFilename) {
            $mime = mime_content_type($this->getUploadRootDir() . '/' . $this->fileLogo2TemporaryFilename);

            if (false !== $mime) {
                return $mime;
            }
        }

        return null;
    }

    public function getFileLogo2Content(): ?string
    {
        if ($this->fileLogo2TemporaryFilename) {
            return base64_encode(
                file_get_contents($this->getUploadRootDir() . '/' . $this->fileLogo2TemporaryFilename)
            );
        }

        return null;
    }
}
