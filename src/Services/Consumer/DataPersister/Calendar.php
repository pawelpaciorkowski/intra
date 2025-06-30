<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\Calendar as CalendarEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\Calendar as CalendarMapper;
use Doctrine\ORM\EntityManagerInterface;

class Calendar implements DataPersisterInterface
{
    private $entityManager;
    private $periodMapper;

    public function __construct(EntityManagerInterface $entityManager, CalendarMapper $periodMapper)
    {
        $this->entityManager = $entityManager;
        $this->periodMapper = $periodMapper;
    }

    public function persist(array $data): object
    {
        $period = $this->periodMapper->populateData(new CalendarEntity(), $data);

        $this->entityManager->persist($period);
        $this->entityManager->flush();

        return $period;
    }

    /**
     * @throws DataPersisterException
     */
    public function update(array $data): object
    {
        $period = $this->entityManager->getRepository(CalendarEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$period) {
            throw new DataPersisterException(sprintf('Calendar with UUID %s not found', $data['uuid']));
        }

        $this->periodMapper->populateData($period, $data);
        $this->entityManager->flush();

        return $period;
    }

    /**
     * @throws DataPersisterException
     */
    public function remove(array $data): void
    {
        $period = $this->entityManager->getRepository(CalendarEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$period) {
            throw new DataPersisterException(sprintf('Calendar with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($period);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
