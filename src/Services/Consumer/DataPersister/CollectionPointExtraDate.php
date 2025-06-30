<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\CollectionPointExtraDate as CollectionPointExtraDateEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\CollectionPointExtraDate as CollectionPointExtraDateMapper;
use Doctrine\ORM\EntityManagerInterface;

class CollectionPointExtraDate implements DataPersisterInterface
{
    private $entityManager;
    private $collectionPointExtraDateMapper;

    public function __construct(
        EntityManagerInterface $entityManager,
        CollectionPointExtraDateMapper $collectionPointExtraDateMapper
    ) {
        $this->entityManager = $entityManager;
        $this->collectionPointExtraDateMapper = $collectionPointExtraDateMapper;
    }

    public function persist(array $data): object
    {
        $collectionPointExtraDate = $this->collectionPointExtraDateMapper->populateData(
            new CollectionPointExtraDateEntity(),
            $data
        );

        $this->entityManager->persist($collectionPointExtraDate);
        $this->entityManager->flush();

        return $collectionPointExtraDate;
    }

    public function update(array $data): object
    {
        $collectionPointExtraDate = $this->entityManager->getRepository(
            CollectionPointExtraDateEntity::class
        )->findOneBy(['uuid' => $data['uuid']]);

        if (!$collectionPointExtraDate) {
            throw new DataPersisterException(sprintf('CollectionPointExtraDate with UUID %s not found', $data['uuid']));
        }

        $this->collectionPointExtraDateMapper->populateData($collectionPointExtraDate, $data);
        $this->entityManager->flush();

        return $collectionPointExtraDate;
    }

    public function remove(array $data): void
    {
        $collectionPointExtraDate = $this->entityManager->getRepository(
            CollectionPointExtraDateEntity::class
        )->findOneBy(['uuid' => $data['uuid']]);

        if (!$collectionPointExtraDate) {
            throw new DataPersisterException(sprintf('CollectionPointExtraDate with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($collectionPointExtraDate);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
