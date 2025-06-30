<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\LabGroup as LabGroupEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\LabGroup as LabGroupMapper;
use Doctrine\ORM\EntityManagerInterface;

class LabGroup implements DataPersisterInterface
{
    private $entityManager;
    private $labGroupMapper;

    public function __construct(EntityManagerInterface $entityManager, LabGroupMapper $labGroupMapper)
    {
        $this->entityManager = $entityManager;
        $this->labGroupMapper = $labGroupMapper;
    }

    public function persist(array $data): object
    {
        $labGroup = $this->labGroupMapper->populateData(new LabGroupEntity(), $data);

        $this->entityManager->persist($labGroup);
        $this->entityManager->flush();

        return $labGroup;
    }

    public function update(array $data): object
    {
        $labGroup = $this->entityManager->getRepository(LabGroupEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$labGroup) {
            throw new DataPersisterException(sprintf('LabGroup with UUID %s not found', $data['uuid']));
        }

        $this->labGroupMapper->populateData($labGroup, $data);
        $this->entityManager->flush();

        return $labGroup;
    }

    public function remove(array $data): void
    {
        $labGroup = $this->entityManager->getRepository(LabGroupEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$labGroup) {
            throw new DataPersisterException(sprintf('LabGroup with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($labGroup);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
