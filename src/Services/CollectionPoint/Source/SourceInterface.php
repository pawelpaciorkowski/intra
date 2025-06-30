<?php

declare(strict_types=1);

namespace App\Services\CollectionPoint\Source;

use App\Services\CollectionPoint\Data\DataInterface;

interface SourceInterface
{
    public function getData(): DataInterface;
}
