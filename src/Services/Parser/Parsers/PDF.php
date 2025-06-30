<?php

declare(strict_types=1);

namespace App\Services\Parser\Parsers;

use Smalot\PdfParser\Parser;

class PDF implements ParserInterface
{
    public function parse(string $file): string
    {
        try {
            return (new Parser())->parseFile($file)->getText();
        } catch (\Exception) {
            return '';
        }
    }
}
