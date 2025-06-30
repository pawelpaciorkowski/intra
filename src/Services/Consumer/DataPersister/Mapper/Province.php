<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister\Mapper;

class Province implements ConsumerMapperInterface
{
    public function populateData(object $object, array $data): object
    {
        $object->setUuid($data['uuid']);

        if (array_key_exists('name', $data)) {
            $object->setName($data['name']);
        }

        if (array_key_exists('teryt', $data)) {
            $object->setTeryt((int)$data['teryt']);
        }

        if (array_key_exists('latitude', $data)) {
            $object->setLatitude($data['latitude']);
        }

        if (array_key_exists('longitude', $data)) {
            $object->setLongitude($data['longitude']);
        }

        if (array_key_exists('zoom', $data)) {
            $object->setZoom($data['zoom']);
        }

        return $object;
    }
}
