<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\Period as PeriodEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\Period as PeriodMapper;
use Doctrine\ORM\EntityManagerInterface;

class Period implements DataPersisterInterface
{
    private $entityManager;
    private $periodMapper;

    public function __construct(EntityManagerInterface $entityManager, PeriodMapper $periodMapper)
    {
        $this->entityManager = $entityManager;
        $this->periodMapper = $periodMapper;
    }

    public function persist(array $data): object
    {
        $period = $this->periodMapper->populateData(new PeriodEntity(), $data);

        $this->entityManager->persist($period);
        $this->entityManager->flush();

        return $period;
    }

    public function update(array $data): object
    {
        $period = $this->entityManager->getRepository(PeriodEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$period) {
            throw new DataPersisterException(sprintf('Period with UUID %s not found', $data['uuid']));
        }

        $this->periodMapper->populateData($period, $data);
        $this->entityManager->flush();

        return $period;
    }

    public function remove(array $data): void
    {
        $period = $this->entityManager->getRepository(PeriodEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$period) {
            throw new DataPersisterException(sprintf('Period with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($period);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
