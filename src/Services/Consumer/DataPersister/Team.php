<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\Team as TeamEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\Team as TeamMapper;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Component\ParameterBag;

class Team implements DataPersisterInterface
{
    private $entityManager;
    private $teamMapper;

    public function __construct(EntityManagerInterface $entityManager, TeamMapper $teamMapper)
    {
        $this->entityManager = $entityManager;
        $this->teamMapper = $teamMapper;
    }

    public function persist(array $data): object
    {
        throw new DataPersisterException('not allowed');
    }

    public function update(array $data): object
    {
        $team = $this->entityManager->getRepository(TeamEntity::class)->findAllByParams(
            new ParameterBag([
                'uuid' => $data['uuid'],
                'restrictForApi' => true,
            ])
        );

        if (!$team) {
            throw new DataPersisterException(sprintf('Team with UUID %s not found', $data['uuid']));
        }

        $this->teamMapper->populateData($team, $data);
        $this->entityManager->flush();

        return $team;
    }

    public function remove(array $data): void
    {
        throw new DataPersisterException('not allowed');
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
