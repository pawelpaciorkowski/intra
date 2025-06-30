<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\Province as ProvinceEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\Province as ProvinceMapper;
use Doctrine\ORM\EntityManagerInterface;

class Province implements DataPersisterInterface
{
    private $entityManager;
    private $provinceMapper;

    public function __construct(EntityManagerInterface $entityManager, ProvinceMapper $provinceMapper)
    {
        $this->entityManager = $entityManager;
        $this->provinceMapper = $provinceMapper;
    }

    public function persist(array $data): object
    {
        $province = $this->provinceMapper->populateData(new ProvinceEntity(), $data);

        $this->entityManager->persist($province);
        $this->entityManager->flush();

        return $province;
    }

    public function update(array $data): object
    {
        $province = $this->entityManager->getRepository(ProvinceEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$province) {
            throw new DataPersisterException(sprintf('Province with UUID %s not found', $data['uuid']));
        }

        $this->provinceMapper->populateData($province, $data);
        $this->entityManager->flush();

        return $province;
    }

    public function remove(array $data): void
    {
        $province = $this->entityManager->getRepository(ProvinceEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$province) {
            throw new DataPersisterException(sprintf('Province with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($province);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
