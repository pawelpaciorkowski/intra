<?php

declare(strict_types=1);

namespace App\Services\Parser\Parsers;

interface ParserInterface
{
    public function parse(string $file): string;
}
