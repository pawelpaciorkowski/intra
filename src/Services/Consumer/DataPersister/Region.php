<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\Region as RegionEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\Region as RegionMapper;
use Doctrine\ORM\EntityManagerInterface;

class Region implements DataPersisterInterface
{
    private $entityManager;
    private $regionMapper;

    public function __construct(EntityManagerInterface $entityManager, RegionMapper $regionMapper)
    {
        $this->entityManager = $entityManager;
        $this->regionMapper = $regionMapper;
    }

    public function persist(array $data): object
    {
        $region = $this->regionMapper->populateData(new RegionEntity(), $data);

        $this->entityManager->persist($region);
        $this->entityManager->flush();

        return $region;
    }

    public function update(array $data): object
    {
        $region = $this->entityManager->getRepository(RegionEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$region) {
            throw new DataPersisterException(sprintf('Region with UUID %s not found', $data['uuid']));
        }

        $this->regionMapper->populateData($region, $data);
        $this->entityManager->flush();

        return $region;
    }

    public function remove(array $data): void
    {
        $region = $this->entityManager->getRepository(RegionEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$region) {
            throw new DataPersisterException(sprintf('Region with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($region);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
