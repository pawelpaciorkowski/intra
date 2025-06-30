<?php

declare(strict_types=1);

namespace App\Services\Export\Generator;

use App\Services\Export\Map\MapInterface;
use DateTime;
use IntlDateFormatter;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function fclose;
use function fopen;
use function fputcsv;
use function sprintf;

final class Csv implements GeneratorInterface
{
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
        $data = $this->data;
        $map = $this->map;

        $response = new StreamedResponse(static function () use ($map, $data): void {
            $handle = fopen('php://output', 'rb+');

            $header = [];
            foreach ($map->getColumnNames() as $columnName) {
                $header[] = $columnName;
            }
            fputcsv($handle, $header);

            foreach ($data as $row) {
                fputcsv($handle, $map->getRow($row));
            }

            fclose($handle);
        });

        $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::SHORT, IntlDateFormatter::MEDIUM);

        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set(
            'Content-Disposition',
            sprintf('attachment; filename="%s_%s.csv"', $filename, $formatter->format(new DateTime()))
        );

        return $response;
    }
}
