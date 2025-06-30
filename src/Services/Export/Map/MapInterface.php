<?php

declare(strict_types=1);

namespace App\Services\Export\Map;

interface MapInterface
{
    public function setColumns(?array $columns);

    public function getColumnNames(): array;

    public function getRow($object): array;
}
