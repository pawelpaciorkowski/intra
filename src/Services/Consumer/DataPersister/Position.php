<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\Position as PositionEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\Position as PositionMapper;
use Doctrine\ORM\EntityManagerInterface;

class Position implements DataPersisterInterface
{
    private $entityManager;
    private $positionMapper;

    public function __construct(EntityManagerInterface $entityManager, PositionMapper $positionMapper)
    {
        $this->entityManager = $entityManager;
        $this->positionMapper = $positionMapper;
    }

    public function persist(array $data): object
    {
        $position = $this->positionMapper->populateData(new PositionEntity(), $data);

        $this->entityManager->persist($position);
        $this->entityManager->flush();

        return $position;
    }

    public function update(array $data): object
    {
        $position = $this->entityManager->getRepository(PositionEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$position) {
            throw new DataPersisterException(sprintf('Position with UUID %s not found', $data['uuid']));
        }

        $this->positionMapper->populateData($position, $data);
        $this->entityManager->flush();

        return $position;
    }

    public function remove(array $data): void
    {
        $position = $this->entityManager->getRepository(PositionEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$position) {
            throw new DataPersisterException(sprintf('Position with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($position);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
