<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister\Mapper;

use App\Entity\Province;
use Doctrine\ORM\EntityManagerInterface;

class City implements ConsumerMapperInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function populateData(object $object, array $data): object
    {
        $object->setUuid($data['uuid']);

        if (array_key_exists('name', $data)) {
            $object->setName($data['name']);
        }

        if (array_key_exists('latitude', $data)) {
            $object->setLatitude($data['latitude']);
        }

        if (array_key_exists('longitude', $data)) {
            $object->setLongitude($data['longitude']);
        }

        if (array_key_exists('province', $data)) {
            if (is_array($data['province']) && array_key_exists('uuid', $data['province'])) {
                $object->setProvince(
                    $this->entityManager->getRepository(Province::class)->findOneBy(
                        ['uuid' => $data['province']['uuid']]
                    )
                );
            } else {
                $object->setProvince(null);
            }
        }

        if (array_key_exists('zoom', $data)) {
            $object->setZoom($data['zoom']);
        }

        return $object;
    }
}
