<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister\Mapper;

use App\Entity\LabGroup;
use App\Entity\Laboratory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class Lab implements ConsumerMapperInterface
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

        if (array_key_exists('symbol', $data)) {
            $object->setSymbol($data['symbol']);
        }

        if (array_key_exists('labGroup', $data)) {
            if (is_array($data['labGroup']) && array_key_exists('uuid', $data['labGroup'])) {
                $object->setLabGroup(
                    $this->entityManager->getRepository(LabGroup::class)->findOneBy(
                        ['uuid' => $data['labGroup']['uuid']]
                    )
                );
            } else {
                $object->setLabGroup(null);
            }
        }

        if (array_key_exists('laboratory', $data)) {
            if (is_array($data['laboratory']) && array_key_exists('uuid', $data['laboratory'])) {
                $object->setLaboratory(
                    $this->entityManager->getRepository(Laboratory::class)->findOneBy(
                        ['uuid' => $data['laboratory']['uuid']]
                    )
                );
            } else {
                $object->setLaboratory(null);
            }
        }

        if (array_key_exists('user', $data)) {
            if (is_array($data['user']) && array_key_exists('uuid', $data['user'])) {
                $object->setUser(
                    $this->entityManager->getRepository(User::class)->findOneBy(['uuid' => $data['user']['uuid']])
                );
            } else {
                $object->setUser(null);
            }
        }

        return $object;
    }
}
