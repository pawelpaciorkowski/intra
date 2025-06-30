<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister\Mapper;

use App\Entity\Position;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Component\ParameterBag;

class User implements ConsumerMapperInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function populateData(object $object, array $data): object
    {
        $object->setUuid($data['uuid']);

        if (array_key_exists('name', $data)) {
            $object->setName($data['name']);
        }

        if (array_key_exists('surname', $data)) {
            $object->setSurname($data['surname']);
        }

        if (array_key_exists('username', $data)) {
            $object->setUsername($data['username']);
        }

        if (array_key_exists('email', $data)) {
            $object->setEmail($data['email']);
        }

        if (array_key_exists('team', $data)) {
            if (is_array($data['team']) && array_key_exists('uuid', $data['team'])) {
                $object->setTeam(
                    $this->entityManager->getRepository(Team::class)->findAllByParams(
                        new ParameterBag([
                            'uuid' => $data['team']['uuid'],
                            'restrictForApi' => true,
                        ])
                    )
                );
            } else {
                $object->setTeam();
            }
        }

        if (array_key_exists('position', $data)) {
            if (is_array($data['position']) && array_key_exists('uuid', $data['position'])) {
                $object->setPosition(
                    $this
                        ->entityManager
                        ->getRepository(Position::class)
                        ->findOneBy(['uuid' => $data['position']['uuid']])
                );
            } else {
                $object->setPosition();
            }
        }

        return $object;
    }
}
