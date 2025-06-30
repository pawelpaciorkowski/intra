<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\Lab as LabEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\Lab as LabMapper;
use Doctrine\ORM\EntityManagerInterface;

class Lab implements DataPersisterInterface
{
    private $entityManager;
    private $labMapper;

    public function __construct(EntityManagerInterface $entityManager, LabMapper $labMapper)
    {
        $this->entityManager = $entityManager;
        $this->labMapper = $labMapper;
    }

    public function persist(array $data): object
    {
        $lab = $this->labMapper->populateData(new LabEntity(), $data);

        $this->entityManager->persist($lab);
        $this->entityManager->flush();

        return $lab;
    }

    public function update(array $data): object
    {
        $lab = $this->entityManager->getRepository(LabEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$lab) {
            throw new DataPersisterException(sprintf('Lab with UUID %s not found', $data['uuid']));
        }

        $this->labMapper->populateData($lab, $data);
        $this->entityManager->flush();

        return $lab;
    }

    public function remove(array $data): void
    {
        $lab = $this->entityManager->getRepository(LabEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$lab) {
            throw new DataPersisterException(sprintf('Lab with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($lab);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
