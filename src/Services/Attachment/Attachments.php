<?php

declare(strict_types=1);

namespace App\Services\Attachment;

use Countable;

use function count;

final class Attachments implements Countable
{
    private array $attachments = [];

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function setAttachments(array $attachments): void
    {
        $this->attachments = $attachments;
    }

    public function pushAttachment(Attachment $attachment): void
    {
        $this->attachments[] = $attachment;
    }

    public function count(): int
    {
        return count($this->attachments);
    }
}
