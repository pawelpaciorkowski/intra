<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ISOFileRepository;
use App\Services\Parser\ParserService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Random\RandomException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "iso_files")]
#[ORM\Entity(repositoryClass: ISOFileRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class ISOFile implements IndexInterface
{
    use TimestampableEntity;

    public const string FILE_UPLOAD_DIR = 'iso';

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[Gedmo\Versioned]
    #[ORM\Column]
    private bool $isActive;

    #[ORM\Column(length: 256)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 256)]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $description = null;

    #[ORM\Column(type: 'smallint')]
    private int $sort;

    #[ORM\ManyToOne(inversedBy: "ISOFiles")]
    #[JoinColumn]
    private ?ISOCategory $ISOCategory = null;

    #[Assert\File(maxSize: '128M', extensions: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'pptx', 'odt', 'jpg', 'jpeg', 'gif', 'png', 'heic', 'bmp', 'tiff', 'svg', 'webp', 'zip'])]
    private ?UploadedFile $currentFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Gedmo\Versioned]
    private ?string $currentFileFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Gedmo\Versioned]
    private ?string $currentFileOriginalFilename = null;

    #[Assert\File(maxSize: '128M', extensions: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'pptx', 'odt', 'jpg', 'jpeg', 'gif', 'png', 'heic', 'bmp', 'tiff', 'svg', 'webp', 'zip'])]
    private ?UploadedFile $originalFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Gedmo\Versioned]
    private ?string $originalFileFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Gedmo\Versioned]
    private ?string $originalFileOriginalFilename = null;

    #[ORM\OneToMany(targetEntity: "ISOFileFileHistory", mappedBy: "ISOFile")]
    #[ORM\OrderBy(["createdAt" => "desc"])]
    private Collection $ISOFileFileHistory;

    #[ORM\ManyToMany(targetEntity: Tag::class, cascade: ['persist'])]
    #[ORM\JoinTable(name: 'iso_files_tags')]
    private Collection $tags;

    public function __construct()
    {
        $this->isActive = false;
        $this->sort = 0;
        $this->ISOFileFileHistory = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    public function getISOCategory(): ?ISOCategory
    {
        return $this->ISOCategory;
    }

    public function setISOCategory(?ISOCategory $ISOCategory): self
    {
        $this->ISOCategory = $ISOCategory;

        return $this;
    }

    /**
     * @throws RandomException
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function preUpload(): void
    {
        if (null !== $this->getCurrentFile()) {
            $filename = bin2hex(random_bytes(20));
            $this->currentFileFilename = $filename . '.' . $this->getCurrentFile()->guessExtension();
            $this->currentFileOriginalFilename = $this->getCurrentFile()->getClientOriginalName();
        }

        if (null !== $this->getOriginalFile()) {
            $filename = bin2hex(random_bytes(20));
            $this->originalFileFilename = $filename . '.' . $this->getOriginalFile()->guessExtension();
            $this->originalFileOriginalFilename = $this->getOriginalFile()->getClientOriginalName();
        }
    }

    #[ORM\PostPersist]
    #[ORM\PostUpdate]
    public function upload(): void
    {
        if (null !== $this->getCurrentFile()) {
            $this->getCurrentFile()->move($this->getUploadRootDir(), $this->currentFileFilename);

            $this->currentFile = null;
        }

        if (null !== $this->getOriginalFile()) {
            $this->getOriginalFile()->move($this->getUploadRootDir(), $this->originalFileFilename);

            $this->originalFile = null;
        }
    }

    protected function getUploadRootDir(): string
    {
        return __DIR__ . '/../../' . $this->getUploadDir();
    }

    protected function getUploadDir(): string
    {
        return 'uploads/' . $this::FILE_UPLOAD_DIR;
    }

    public function getCurrentFile(): ?UploadedFile
    {
        return $this->currentFile;
    }

    public function setCurrentFile(?UploadedFile $currentFile): self
    {
        $this->currentFile = $currentFile;

        if (null !== $this->currentFileFilename) {
            $this->currentFileFilename = null;
        } else {
            $this->currentFileFilename = 'initial';
        }

        return $this;
    }

    public function getCurrentFileFilename(): ?string
    {
        return $this->currentFileFilename;
    }

    public function setCurrentFileFilename(?string $currentFileFilename): self
    {
        $this->currentFileFilename = $currentFileFilename;

        return $this;
    }

    public function getCurrentFileOriginalFilename(): ?string
    {
        return $this->currentFileOriginalFilename;
    }

    public function setCurrentFileOriginalFilename(?string $currentFileOriginalFilename): self
    {
        $this->currentFileOriginalFilename = $currentFileOriginalFilename;

        return $this;
    }

    public function getOriginalFile(): ?UploadedFile
    {
        return $this->originalFile;
    }

    public function setOriginalFile(?UploadedFile $originalFile): self
    {
        $this->originalFile = $originalFile;

        if (null !== $this->originalFileFilename) {
            $this->originalFileFilename = null;
        } else {
            $this->originalFileFilename = 'initial';
        }

        return $this;
    }

    public function getOriginalFileFilename(): ?string
    {
        return $this->originalFileFilename;
    }

    public function setOriginalFileFilename(?string $originalFileFilename): self
    {
        $this->originalFileFilename = $originalFileFilename;

        return $this;
    }

    public function getOriginalFileOriginalFilename(): ?string
    {
        return $this->originalFileOriginalFilename;
    }

    public function setOriginalFileOriginalFilename(?string $originalFileOriginalFilename): self
    {
        $this->originalFileOriginalFilename = $originalFileOriginalFilename;

        return $this;
    }

    public function getOriginalFileAbsolutePath(): ?string
    {
        return null === $this->originalFileFilename ? null : $this->getUploadRootDir(
        ) . '/' . $this->originalFileFilename;
    }

    public function getCurrentFileAbsolutePath(): ?string
    {
        return null === $this->currentFileFilename ? null : $this->getUploadRootDir(
        ) . '/' . $this->currentFileFilename;
    }

    public function addISOFileFileHistory(ISOFileFileHistory $ISOFileFileHistory): self
    {
        $this->ISOFileFileHistory[] = $ISOFileFileHistory;

        return $this;
    }

    public function removeISOFileFileHistory(ISOFileFileHistory $ISOFileFileHistory): void
    {
        $this->ISOFileFileHistory->removeElement($ISOFileFileHistory);
    }

    public function getISOFileFileHistory(): ?Collection
    {
        return $this->ISOFileFileHistory;
    }

    public function getFileHistory(): ?Collection
    {
        return $this->getISOFileFileHistory();
    }

    public function getIsIndex(): bool
    {
        return $this->isActive;
    }

    public function getDataForIndex(): array
    {
        $file = $this->getOriginalFileAbsolutePath();
        if ($this->getCurrentFileAbsolutePath()) {
            $file = $this->getCurrentFileAbsolutePath();
        }

        return array_filter([
            'title' => $this->name,
            'description' => $this->description ? strip_tags($this->description) : null,
            'content' => ParserService::parse($file),
        ], static function ($e) {
            return !empty($e);
        });
    }

    public function getNameForIndex(): string
    {
        return $this->name;
    }

    public function getDescriptionForIndex(): ?string
    {
        if (!$this->description) {
            return null;
        }

        $text = strip_tags($this->description);

        if (strlen($text) > 512) {
            $text = substr($text, 0, 512) . '...';
        }

        return $text;
    }

    public function getPriority(): int
    {
        return 25;
    }

    public function addTag(Tag $tag): self
    {
        $this->tags[] = $tag;

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getTags(): ?Collection
    {
        return $this->tags;
    }

    public function setTags(Collection|array $tags): self
    {
        $this->tags = new ArrayCollection($tags);

        return $this;
    }
}
