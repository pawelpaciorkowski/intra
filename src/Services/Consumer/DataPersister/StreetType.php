<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\StreetType as StreetTypeEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\StreetType as StreetTypeMapper;
use Doctrine\ORM\EntityManagerInterface;

class StreetType implements DataPersisterInterface
{
    private $entityManager;
    private $regionMapper;

    public function __construct(EntityManagerInterface $entityManager, StreetTypeMapper $regionMapper)
    {
        $this->entityManager = $entityManager;
        $this->regionMapper = $regionMapper;
    }

    public function persist(array $data): object
    {
        $region = $this->regionMapper->populateData(new StreetTypeEntity(), $data);

        $this->entityManager->persist($region);
        $this->entityManager->flush();

        return $region;
    }

    public function update(array $data): object
    {
        $region = $this->entityManager->getRepository(StreetTypeEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$region) {
            throw new DataPersisterException(sprintf('StreetType with UUID %s not found', $data['uuid']));
        }

        $this->regionMapper->populateData($region, $data);
        $this->entityManager->flush();

        return $region;
    }

    public function remove(array $data): void
    {
        $region = $this->entityManager->getRepository(StreetTypeEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$region) {
            throw new DataPersisterException(sprintf('StreetType with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($region);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
