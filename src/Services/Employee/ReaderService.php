<?php

declare(strict_types=1);

namespace App\Services\Employee;

use App\Services\Employee\Entity\Department;
use App\Services\Employee\Entity\Employee;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ReaderService
{
    public function read(UploadedFile $file): array
    {
        $reader = IOFactory::createReaderForFile($file->getRealPath());
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getRealPath());

        return [
            $this->fulfillData(Employee::class, $spreadsheet->getSheet(0)),
            $this->fulfillData(Department::class, $spreadsheet->getSheet(1))
        ];
    }

    private function fulfillData(string $className, Worksheet $worksheet): array
    {
        $data = [];

        $isFirstRow = true;
        foreach ($worksheet->getRowIterator() as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getValue();
            }
            if (count(array_filter($cells))) {
                $data[] = $className::fromArray($cells);
            }
        }

        return $data;
    }
}
