<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister\Mapper;

class CustomerService implements ConsumerMapperInterface
{
    public function populateData(object $object, array $data): object
    {
        $object->setUuid($data['uuid']);

        if (array_key_exists('name', $data)) {
            $object->setName($data['name']);
        }

        if (array_key_exists('email', $data)) {
            $object->setEmail($data['email']);
        }

        if (array_key_exists('description', $data)) {
            $object->setDescription($data['description']);
        }

        return $object;
    }
}
