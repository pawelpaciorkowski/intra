<?php

declare(strict_types=1);

namespace App\Services\Attachment;

final class Attachment
{
    private string $content;
    private string $filename;
    private ?string $contentType = null;

    public static function create(
        string $content,
        string $filename,
        ?string $contentType = null
    ): Attachment {
        return (new self())
            ->setContent($content)
            ->setFilename($filename)
            ->setContentType($contentType);
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(?string $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }
}
