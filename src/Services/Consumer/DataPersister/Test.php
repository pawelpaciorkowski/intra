<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\Test as TestEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\Test as TestMapper;
use Doctrine\ORM\EntityManagerInterface;

class Test implements DataPersisterInterface
{
    private $entityManager;
    private $testMapper;

    public function __construct(EntityManagerInterface $entityManager, TestMapper $testMapper)
    {
        $this->entityManager = $entityManager;
        $this->testMapper = $testMapper;
    }

    public function persist(array $data): object
    {
        $test = $this->testMapper->populateData(new TestEntity(), $data);

        $this->entityManager->persist($test);
        $this->entityManager->flush();

        return $test;
    }

    public function update(array $data): object
    {
        $test = $this->entityManager->getRepository(TestEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$test) {
            throw new DataPersisterException(sprintf('Test with UUID %s not found', $data['uuid']));
        }

        $this->testMapper->populateData($test, $data);
        $this->entityManager->flush();

        return $test;
    }

    public function remove(array $data): void
    {
        $test = $this->entityManager->getRepository(TestEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$test) {
            throw new DataPersisterException(sprintf('Test with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($test);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
