<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\CollectionPointClassification as CollectionPointClassificationEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\CollectionPointClassification as CollectionPointClassificationMapper;
use Doctrine\ORM\EntityManagerInterface;

class CollectionPointClassification implements DataPersisterInterface
{
    private $entityManager;
    private $collectionPointClassificationMapper;

    public function __construct(
        EntityManagerInterface $entityManager,
        CollectionPointClassificationMapper $collectionPointClassificationMapper
    ) {
        $this->entityManager = $entityManager;
        $this->collectionPointClassificationMapper = $collectionPointClassificationMapper;
    }

    public function persist(array $data): object
    {
        $collectionPointClassification = $this->collectionPointClassificationMapper->populateData(
            new CollectionPointClassificationEntity(),
            $data
        );

        $this->entityManager->persist($collectionPointClassification);
        $this->entityManager->flush();

        return $collectionPointClassification;
    }

    /**
     * @throws DataPersisterException
     */
    public function update(array $data): object
    {
        $collectionPointClassification = $this->entityManager->getRepository(
            CollectionPointClassificationEntity::class
        )->findOneBy(['uuid' => $data['uuid']]);

        if (!$collectionPointClassification) {
            throw new DataPersisterException(
                sprintf('CollectionPointClassification with UUID %s not found', $data['uuid'])
            );
        }

        $this->collectionPointClassificationMapper->populateData($collectionPointClassification, $data);
        $this->entityManager->flush();

        return $collectionPointClassification;
    }

    /**
     * @throws DataPersisterException
     */
    public function remove(array $data): void
    {
        $collectionPointClassification = $this->entityManager->getRepository(
            CollectionPointClassificationEntity::class
        )->findOneBy(['uuid' => $data['uuid']]);

        if (!$collectionPointClassification) {
            throw new DataPersisterException(
                sprintf('CollectionPointClassification with UUID %s not found', $data['uuid'])
            );
        }

        $this->entityManager->remove($collectionPointClassification);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
