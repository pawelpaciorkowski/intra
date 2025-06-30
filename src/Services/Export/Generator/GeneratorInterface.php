<?php

declare(strict_types=1);

namespace App\Services\Export\Generator;

use App\Services\Export\Map\MapInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface GeneratorInterface
{
    public function setMap(MapInterface $map);

    public function setData(array $data);

    public function setLocale(string $locale);

    public function generate(string $filename): StreamedResponse;
}
