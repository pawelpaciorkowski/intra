<?php

declare(strict_types=1);

namespace App\Services\Attachment;

use function is_string;
use function preg_match;

final class ParameterParser
{
    public static function parse(array $parameters, array $data): array
    {
        $return = [];

        foreach ($parameters as $parameter) {
            if (is_string($parameter) && preg_match('/^\$([\w_]+)$/', $parameter, $match)) {
                $return[] = $data[$match[1]];
            } else {
                $return[] = $parameter;
            }
        }

        return $return;
    }
}
