<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\CollectionPointCloseDate as CollectionPointCloseDateEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\CollectionPointCloseDate as CollectionPointCloseDateMapper;
use Doctrine\ORM\EntityManagerInterface;

class CollectionPointCloseDate implements DataPersisterInterface
{
    private $entityManager;
    private $collectionPointCloseDateMapper;

    public function __construct(
        EntityManagerInterface $entityManager,
        CollectionPointCloseDateMapper $collectionPointCloseDateMapper
    ) {
        $this->entityManager = $entityManager;
        $this->collectionPointCloseDateMapper = $collectionPointCloseDateMapper;
    }

    public function persist(array $data): object
    {
        $collectionPointCloseDate = $this->collectionPointCloseDateMapper->populateData(
            new CollectionPointCloseDateEntity(),
            $data
        );

        $this->entityManager->persist($collectionPointCloseDate);
        $this->entityManager->flush();

        return $collectionPointCloseDate;
    }

    /**
     * @throws DataPersisterException
     */
    public function update(array $data): object
    {
        $collectionPointCloseDate = $this->entityManager->getRepository(
            CollectionPointCloseDateEntity::class
        )->findOneBy(['uuid' => $data['uuid']]);

        if (!$collectionPointCloseDate) {
            throw new DataPersisterException(sprintf('CollectionPointCloseDate with UUID %s not found', $data['uuid']));
        }

        $this->collectionPointCloseDateMapper->populateData($collectionPointCloseDate, $data);
        $this->entityManager->flush();

        return $collectionPointCloseDate;
    }

    /**
     * @throws DataPersisterException
     */
    public function remove(array $data): void
    {
        $collectionPointCloseDate = $this->entityManager->getRepository(
            CollectionPointCloseDateEntity::class
        )->findOneBy(['uuid' => $data['uuid']]);

        if (!$collectionPointCloseDate) {
            throw new DataPersisterException(sprintf('CollectionPointCloseDate with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($collectionPointCloseDate);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
