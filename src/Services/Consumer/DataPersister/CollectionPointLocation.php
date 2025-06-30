<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\CollectionPointLocation as CollectionPointLocationEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\CollectionPointLocation as CollectionPointLocationMapper;
use Doctrine\ORM\EntityManagerInterface;

class CollectionPointLocation implements DataPersisterInterface
{
    private $entityManager;
    private $collectionPointLocationMapper;

    public function __construct(
        EntityManagerInterface $entityManager,
        CollectionPointLocationMapper $collectionPointLocationMapper
    ) {
        $this->entityManager = $entityManager;
        $this->collectionPointLocationMapper = $collectionPointLocationMapper;
    }

    public function persist(array $data): object
    {
        $collectionPointLocation = $this->collectionPointLocationMapper->populateData(
            new CollectionPointLocationEntity(),
            $data
        );

        $this->entityManager->persist($collectionPointLocation);
        $this->entityManager->flush();

        return $collectionPointLocation;
    }

    public function update(array $data): object
    {
        $collectionPointLocation = $this->entityManager->getRepository(CollectionPointLocationEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$collectionPointLocation) {
            throw new DataPersisterException(sprintf('CollectionPointLocation with UUID %s not found', $data['uuid']));
        }

        $this->collectionPointLocationMapper->populateData($collectionPointLocation, $data);
        $this->entityManager->flush();

        return $collectionPointLocation;
    }

    public function remove(array $data): void
    {
        $collectionPointLocation = $this->entityManager->getRepository(CollectionPointLocationEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$collectionPointLocation) {
            throw new DataPersisterException(sprintf('CollectionPointLocation with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($collectionPointLocation);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
