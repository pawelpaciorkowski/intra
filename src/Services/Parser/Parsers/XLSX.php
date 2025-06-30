<?php

declare(strict_types=1);

namespace App\Services\Parser\Parsers;

use PhpOffice\PhpSpreadsheet\IOFactory;

class XLSX implements ParserInterface
{
    public function parse(string $file): string
    {
        $spreadsheet = IOFactory::load($file);

        $return = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $return[] = trim($sheet->getTitle());

            $rows = $sheet->toArray();
            foreach ($rows as $row) {
                $return[] = trim(implode("\t", $row));
            }
        }

        return implode(' ', $return);
    }
}
