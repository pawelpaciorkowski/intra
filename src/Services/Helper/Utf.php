<?php

declare(strict_types=1);

namespace App\Services\Helper;

final class Utf
{
    public static function cleanUp(string $string): string
    {
        return mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    }
}
