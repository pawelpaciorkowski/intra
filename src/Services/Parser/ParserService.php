<?php

declare(strict_types=1);

namespace App\Services\Parser;

use App\Services\Parser\Parsers\DOC;
use App\Services\Parser\Parsers\DOCX;
use App\Services\Parser\Parsers\ODT;
use App\Services\Parser\Parsers\PDF;
use App\Services\Parser\Parsers\PPTX;
use App\Services\Parser\Parsers\XLS;
use App\Services\Parser\Parsers\XLSX;
use Exception;
use RuntimeException;

class ParserService
{
    public static function parse(?string $file): string
    {
        if ($file === null) {
            return '';
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        try {
            $parser = match ($extension) {
                'doc' => new DOC(),
                'docx' => new DOCX(),
                'pdf' => new PDF(),
                'xls' => new XLS(),
                'xlsx' => new XLSX(),
                'odt' => new ODT(),
                'pptx' => new PPTX(),
                default => throw new RuntimeException('Invalid extension'),
            };

            return $parser->parse($file);
        } catch (Exception) {
            return '';
        }
    }
}
