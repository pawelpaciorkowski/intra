<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\CollectionPointPartner as CollectionPointPartnerEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\CollectionPointPartner as CollectionPointPartnerMapper;
use Doctrine\ORM\EntityManagerInterface;

class CollectionPointPartner implements DataPersisterInterface
{
    private $entityManager;
    private $collectionPointPartnerMapper;

    public function __construct(
        EntityManagerInterface $entityManager,
        CollectionPointPartnerMapper $collectionPointPartnerMapper
    ) {
        $this->entityManager = $entityManager;
        $this->collectionPointPartnerMapper = $collectionPointPartnerMapper;
    }

    public function persist(array $data): object
    {
        $collectionPointPartner = $this->collectionPointPartnerMapper->populateData(
            new CollectionPointPartnerEntity(),
            $data
        );

        $this->entityManager->persist($collectionPointPartner);
        $this->entityManager->flush();

        return $collectionPointPartner;
    }

    public function update(array $data): object
    {
        $collectionPointPartner = $this->entityManager->getRepository(CollectionPointPartnerEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$collectionPointPartner) {
            throw new DataPersisterException(sprintf('CollectionPointPartner with UUID %s not found', $data['uuid']));
        }

        $this->collectionPointPartnerMapper->populateData($collectionPointPartner, $data);
        $this->entityManager->flush();

        return $collectionPointPartner;
    }

    public function remove(array $data): void
    {
        $collectionPointPartner = $this->entityManager->getRepository(CollectionPointPartnerEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$collectionPointPartner) {
            throw new DataPersisterException(sprintf('CollectionPointPartner with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($collectionPointPartner);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
