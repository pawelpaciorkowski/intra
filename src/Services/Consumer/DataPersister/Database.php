<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\Database as DatabaseEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\Database as DatabaseMapper;
use Doctrine\ORM\EntityManagerInterface;

class Database implements DataPersisterInterface
{
    private $entityManager;
    private $databaseMapper;

    public function __construct(EntityManagerInterface $entityManager, DatabaseMapper $databaseMapper)
    {
        $this->entityManager = $entityManager;
        $this->databaseMapper = $databaseMapper;
    }

    public function persist(array $data): object
    {
        $database = $this->databaseMapper->populateData(new DatabaseEntity(), $data);

        $this->entityManager->persist($database);
        $this->entityManager->flush();

        return $database;
    }

    public function update(array $data): object
    {
        $database = $this->entityManager->getRepository(DatabaseEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$database) {
            throw new DataPersisterException(sprintf('Database with UUID %s not found', $data['uuid']));
        }

        $this->databaseMapper->populateData($database, $data);
        $this->entityManager->flush();

        return $database;
    }

    public function remove(array $data): void
    {
        $database = $this->entityManager->getRepository(DatabaseEntity::class)->findOneBy(['uuid' => $data['uuid']]);

        if (!$database) {
            throw new DataPersisterException(sprintf('Database with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($database);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
