<?php

declare(strict_types=1);

namespace App\Services\Export\Generator;

use App\Services\Export\Map\MapInterface;
use DateTime;
use IntlDateFormatter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxLib;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function sprintf;

final class Xlsx implements GeneratorInterface
{
    private const array HEADER_STYLE = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 11,
            'name' => 'Verdana',
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color' => [
                'rgb' => '23527c',
            ],
        ],
    ];

    private const array BODY_STYLE = [
        'font' => [
            'bold' => false,
            'color' => ['rgb' => '000000'],
            'size' => 11,
            'name' => 'Verdana',
        ],
    ];

    private $map;
    private $data;
    private $locale;

    public function setMap(MapInterface $map): self
    {
        $this->map = $map;

        return $this;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function generate(string $filename): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columnId = 1;
        foreach ($this->map->getColumnNames() as $columnName) {
            $spreadsheet
                ->getActiveSheet()
                ->getColumnDimensionByColumn($columnId)
                ->setWidth(15);

            $sheet
                ->getCellByColumnAndRow($columnId++, 1)
                ->setValue($columnName)
                ->getStyle()
                ->applyFromArray(self::HEADER_STYLE);
        }

        $rowId = 2;
        foreach ($this->data as $row) {
            $columnId = 1;
            foreach ($this->map->getRow($row) as $data) {
                $sheet
                    ->getCellByColumnAndRow($columnId++, $rowId)
                    ->setValue($data)
                    ->getStyle()
                    ->applyFromArray(self::BODY_STYLE);
            }
            ++$rowId;
        }

        $response = new StreamedResponse(static function () use ($spreadsheet): void {
            $writer = new XlsxLib($spreadsheet);
            $writer->save('php://output');
        });

        $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::SHORT, IntlDateFormatter::MEDIUM);

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set(
            'Content-Disposition',
            sprintf('attachment; filename="%s_%s.xlsx"', $filename, $formatter->format(new DateTime()))
        );

        return $response;
    }
}
