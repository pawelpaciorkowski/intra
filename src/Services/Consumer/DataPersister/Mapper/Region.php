<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister\Mapper;

class Region implements ConsumerMapperInterface
{
    public function populateData(object $object, array $data): object
    {
        $object->setUuid($data['uuid']);

        if (array_key_exists('name', $data)) {
            $object->setName($data['name']);
        }

        return $object;
    }
}
