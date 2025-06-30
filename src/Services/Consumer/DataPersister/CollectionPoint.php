<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\CollectionPoint as CollectionPointEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\CollectionPoint as CollectionPointMapper;
use Doctrine\ORM\EntityManagerInterface;

class CollectionPoint implements DataPersisterInterface
{
    private $entityManager;
    private $collectionPointMapper;

    public function __construct(EntityManagerInterface $entityManager, CollectionPointMapper $collectionPointMapper)
    {
        $this->entityManager = $entityManager;
        $this->collectionPointMapper = $collectionPointMapper;
    }

    public function persist(array $data): object
    {
        $collectionPoint = $this->collectionPointMapper->populateData(new CollectionPointEntity(), $data);

        $this->entityManager->persist($collectionPoint);
        $this->entityManager->flush();

        return $collectionPoint;
    }

    /**
     * @throws DataPersisterException
     */
    public function update(array $data): object
    {
        $collectionPoint = $this->entityManager->getRepository(CollectionPointEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$collectionPoint) {
            throw new DataPersisterException(sprintf('CollectionPoint with UUID %s not found', $data['uuid']));
        }

        $this->collectionPointMapper->populateData($collectionPoint, $data);
        $this->entityManager->flush();

        return $collectionPoint;
    }

    /**
     * @throws DataPersisterException
     */
    public function remove(array $data): void
    {
        $collectionPoint = $this->entityManager->getRepository(CollectionPointEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$collectionPoint) {
            throw new DataPersisterException(sprintf('CollectionPoint with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($collectionPoint);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
