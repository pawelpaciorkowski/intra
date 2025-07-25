<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister\Mapper;

class StreetType implements ConsumerMapperInterface
{
    public function populateData(object $object, array $data): object
    {
        $object->setUuid($data['uuid']);

        if (array_key_exists('name', $data)) {
            $object->setName($data['name']);
        }

        if (array_key_exists('short', $data)) {
            $object->setShort($data['short']);
        }

        return $object;
    }
}
