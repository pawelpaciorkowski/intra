<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\Country as CountryEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\Country as CountryMapper;
use Doctrine\ORM\EntityManagerInterface;

class Country implements DataPersisterInterface
{
    private $entityManager;
    private $countryMapper;

    public function __construct(EntityManagerInterface $entityManager, CountryMapper $countryMapper)
    {
        $this->entityManager = $entityManager;
        $this->countryMapper = $countryMapper;
    }

    public function persist(array $data): object
    {
        $country = $this->countryMapper->populateData(new CountryEntity(), $data);

        $this->entityManager->persist($country);
        $this->entityManager->flush();

        return $country;
    }

    public function update(array $data): object
    {
        $country = $this->entityManager->getRepository(CountryEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$country) {
            throw new DataPersisterException(sprintf('Country with UUID %s not found', $data['uuid']));
        }

        $this->countryMapper->populateData($country, $data);
        $this->entityManager->flush();

        return $country;
    }

    public function remove(array $data): void
    {
        $country = $this->entityManager->getRepository(CountryEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$country) {
            throw new DataPersisterException(sprintf('Country with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($country);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
