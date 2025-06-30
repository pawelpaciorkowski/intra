<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister\Mapper;

use App\Entity\Laboratory;
use Doctrine\ORM\EntityManagerInterface;

class Database implements ConsumerMapperInterface
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

        if (array_key_exists('ip', $data)) {
            $object->setIp($data['ip']);
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

        return $object;
    }
}
