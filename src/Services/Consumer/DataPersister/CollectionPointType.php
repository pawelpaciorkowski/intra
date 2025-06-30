<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\CollectionPointType as CollectionPointTypeEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\CollectionPointType as CollectionPointTypeMapper;
use Doctrine\ORM\EntityManagerInterface;

class CollectionPointType implements DataPersisterInterface
{
    private $entityManager;
    private $collectionPointTypeMapper;

    public function __construct(
        EntityManagerInterface $entityManager,
        CollectionPointTypeMapper $collectionPointTypeMapper
    ) {
        $this->entityManager = $entityManager;
        $this->collectionPointTypeMapper = $collectionPointTypeMapper;
    }

    public function persist(array $data): object
    {
        $collectionPointType = $this->collectionPointTypeMapper->populateData(new CollectionPointTypeEntity(), $data);

        $this->entityManager->persist($collectionPointType);
        $this->entityManager->flush();

        return $collectionPointType;
    }

    public function update(array $data): object
    {
        $collectionPointType = $this->entityManager->getRepository(CollectionPointTypeEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$collectionPointType) {
            throw new DataPersisterException(sprintf('CollectionPointType with UUID %s not found', $data['uuid']));
        }

        $this->collectionPointTypeMapper->populateData($collectionPointType, $data);
        $this->entityManager->flush();

        return $collectionPointType;
    }

    public function remove(array $data): void
    {
        $collectionPointType = $this->entityManager->getRepository(CollectionPointTypeEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$collectionPointType) {
            throw new DataPersisterException(sprintf('CollectionPointType with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($collectionPointType);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
