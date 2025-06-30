<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum FileType: string
{
    case CURRENT = 'current';
    case ORIGINAL = 'original';
    public function getLabel(?self $type = null): string
    {
        if (!$type) {
            $type = $this;
        }

        return match ($type) {
            self::CURRENT => 'aktualny',
            self::ORIGINAL => 'oryginalny',
        };
    }
}
