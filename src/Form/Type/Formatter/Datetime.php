<?php

declare(strict_types=1);

namespace App\Form\Type\Formatter;

use IntlDateFormatter;

use function str_replace;

final class Datetime
{
    public static function getDatetimePattern(string $locale): string
    {
        $intlDateFormatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT
        );

        return str_replace(',', '', $intlDateFormatter->getPattern());
    }

    public static function getTimePattern(string $locale): string
    {
        $intlDateFormatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::NONE,
            IntlDateFormatter::SHORT
        );

        return $intlDateFormatter->getPattern();
    }
}
