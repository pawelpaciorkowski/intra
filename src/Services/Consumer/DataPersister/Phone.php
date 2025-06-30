<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\Phone as PhoneEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\Phone as PhoneMapper;
use Doctrine\ORM\EntityManagerInterface;

class Phone implements DataPersisterInterface
{
    private $entityManager;
    private $periodMapper;

    public function __construct(EntityManagerInterface $entityManager, PhoneMapper $periodMapper)
    {
        $this->entityManager = $entityManager;
        $this->periodMapper = $periodMapper;
    }

    public function persist(array $data): object
    {
        $phone = $this->periodMapper->populateData(new PhoneEntity(), $data);

        $this->entityManager->persist($phone);
        $this->entityManager->flush();

        return $phone;
    }

    public function update(array $data): object
    {
        $phone = $this->entityManager->getRepository(PhoneEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$phone) {
            throw new DataPersisterException(sprintf('Phone with UUID %s not found', $data['uuid']));
        }

        $this->periodMapper->populateData($phone, $data);
        $this->entityManager->flush();

        return $phone;
    }

    public function remove(array $data): void
    {
        $phone = $this->entityManager->getRepository(PhoneEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$phone) {
            throw new DataPersisterException(sprintf('Phone with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($phone);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
