<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\City as CityEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\City as CityMapper;
use Doctrine\ORM\EntityManagerInterface;

class City implements DataPersisterInterface
{
    private $entityManager;
    private $cityMapper;

    public function __construct(EntityManagerInterface $entityManager, CityMapper $cityMapper)
    {
        $this->entityManager = $entityManager;
        $this->cityMapper = $cityMapper;
    }

    public function persist(array $data): object
    {
        $city = $this->cityMapper->populateData(new CityEntity(), $data);

        $this->entityManager->persist($city);
        $this->entityManager->flush();

        return $city;
    }

    /**
     * @throws DataPersisterException
     */
    public function update(array $data): object
    {
        $city = $this->entityManager->getRepository(CityEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$city) {
            throw new DataPersisterException(sprintf('City with UUID %s not found', $data['uuid']));
        }

        $this->cityMapper->populateData($city, $data);
        $this->entityManager->flush();

        return $city;
    }

    /**
     * @throws DataPersisterException
     */
    public function remove(array $data): void
    {
        $city = $this->entityManager->getRepository(CityEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$city) {
            throw new DataPersisterException(sprintf('City with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($city);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
