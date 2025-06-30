<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\User as UserEntity;
use App\Services\Component\ParameterBag;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\User as UserMapper;
use Doctrine\ORM\EntityManagerInterface;

class User implements DataPersisterInterface
{
    private $entityManager;
    private $userMapper;

    public function __construct(EntityManagerInterface $entityManager, UserMapper $userMapper)
    {
        $this->entityManager = $entityManager;
        $this->userMapper = $userMapper;
    }

    public function persist(array $data): object
    {
        $user = $this->userMapper->populateData(new UserEntity(), $data);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function update(array $data): object
    {
        $user = $this->entityManager->getRepository(UserEntity::class)->findAllByParams(
            new ParameterBag([
                'uuid' => $data['uuid'],
                'restrictForApi' => true,
            ])
        );

        if (!$user) {
            throw new DataPersisterException(sprintf('User with UUID %s not found', $data['uuid']));
        }

        $this->userMapper->populateData($user, $data);
        $this->entityManager->flush();

        return $user;
    }

    public function remove(array $data): void
    {
        $user = $this->entityManager->getRepository(UserEntity::class)->findAllByParams(
            new ParameterBag([
                'uuid' => $data['uuid'],
                'restrictForApi' => true,
            ])
        );

        if (!$user) {
            throw new DataPersisterException(sprintf('User with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return array_key_exists('team', $data) && in_array(
            $data['team']['uuid'],
            \App\Entity\Team::ALLOWED_ENTITIES,
            true
        );
    }
}
