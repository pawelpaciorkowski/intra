<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\Laboratory as LaboratoryEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\Laboratory as LaboratoryMapper;
use Doctrine\ORM\EntityManagerInterface;

class Laboratory implements DataPersisterInterface
{
    private $entityManager;
    private $laboratoryMapper;

    public function __construct(EntityManagerInterface $entityManager, LaboratoryMapper $laboratoryMapper)
    {
        $this->entityManager = $entityManager;
        $this->laboratoryMapper = $laboratoryMapper;
    }

    public function persist(array $data): object
    {
        $laboratory = $this->laboratoryMapper->populateData(new LaboratoryEntity(), $data);

        $this->entityManager->persist($laboratory);
        $this->entityManager->flush();

        return $laboratory;
    }

    public function update(array $data): object
    {
        $laboratory = $this->entityManager->getRepository(LaboratoryEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$laboratory) {
            throw new DataPersisterException(sprintf('Laboratory with UUID %s not found', $data['uuid']));
        }

        $this->laboratoryMapper->populateData($laboratory, $data);
        $this->entityManager->flush();

        return $laboratory;
    }

    public function remove(array $data): void
    {
        $laboratory = $this->entityManager->getRepository(LaboratoryEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$laboratory) {
            throw new DataPersisterException(sprintf('Laboratory with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($laboratory);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
