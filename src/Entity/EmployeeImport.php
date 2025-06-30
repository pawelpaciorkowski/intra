<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class EmployeeImport
{
    public const string MODE_ADD = 'add';
    public const string MODE_UPDATE = 'update';
    public const string MODE_REPLACE = 'replace';

    public const array MODES = [
        'zaktualizuj pracowników i działy danymi z pliku, usuń rekordy nieodnalezione w pliku' => self::MODE_UPDATE,
        'zaktualizuj pracowników i działy danymi z pliku, nic nie usuwaj' => self::MODE_ADD,
        'zastąp pracowników i działy danymi z pliku, usuń istniejące dane' => self::MODE_REPLACE,
    ];

    #[Assert\NotBlank]
    private ?string $mode = null;

    #[Assert\NotBlank]
    private ?UploadedFile $file = null;

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file = null): static
    {
        $this->file = $file;

        return $this;
    }
}
