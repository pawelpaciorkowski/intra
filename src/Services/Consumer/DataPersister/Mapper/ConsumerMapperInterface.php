<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister\Mapper;

interface ConsumerMapperInterface
{
    public function populateData(object $object, array $data): object;
}
