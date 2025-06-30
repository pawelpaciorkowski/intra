<?php

declare(strict_types=1);

namespace App\Services\CollectionPoint\Source\Helper;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Formatter
{
    private $worksheet;

    public function __construct(Worksheet $worksheet)
    {
        $this->worksheet = $worksheet;
    }

    public function getString(int $col, int $row): ?string
    {
        $value = trim((string)$this->worksheet->getCellByColumnAndRow($col, $row)->getValue());

        if ('' === $value) {
            return null;
        }

        return $value;
    }

    public function getBool(int $col, int $row): bool
    {
        return 'tak' === strtolower((string)$this->worksheet->getCellByColumnAndRow($col, $row)->getValue());
    }
}
