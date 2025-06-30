<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum TargetType: string
{
    case SELF = '_self';
    case BLANK = '_blank';
    case PARENT = '_parent';
    case TOP = '_top';

    public function getLabel(?self $type = null): string
    {
        if (!$type) {
            $type = $this;
        }

        return match ($type) {
            self::SELF => 'self',
            self::BLANK => 'blank',
            self::PARENT => 'parent',
            self::TOP => 'top',
        };
    }
}
