<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister\Mapper;

use App\Entity\CollectionPoint;
use App\Entity\DayOfWeek;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class Period implements ConsumerMapperInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function populateData(object $object, array $data): object
    {
        $object->setUuid($data['uuid']);

        if (array_key_exists('startAt', $data)) {
            $object->setStartAt($data['startAt'] ? new DateTime($data['startAt']) : null);
        }

        if (array_key_exists('endAt', $data)) {
            $object->setEndAt($data['endAt'] ? new DateTime($data['endAt']) : null);
        }

        if (array_key_exists('type', $data)) {
            $object->setType($data['type']);
        }

        if (array_key_exists('isAllDay', $data)) {
            $object->setIsAllDay((bool)$data['isAllDay']);
        }

        if (array_key_exists('collectionPoint', $data)) {
            if (is_array($data['collectionPoint']) && array_key_exists('uuid', $data['collectionPoint'])) {
                $object->setCollectionPoint(
                    $this->entityManager->getRepository(CollectionPoint::class)->findOneBy(
                        ['uuid' => $data['collectionPoint']['uuid']]
                    )
                );
            } else {
                $object->setCollectionPoint(null);
            }
        }

        if (array_key_exists('laboratory', $data)) {
            if (is_array($data['laboratory']) && array_key_exists('uuid', $data['laboratory'])) {
                $object->setLaboratory(
                    $this->entityManager->getRepository(\App\Entity\Laboratory::class)->findOneBy(
                        ['uuid' => $data['laboratory']['uuid']]
                    )
                );
            } else {
                $object->setLaboratory(null);
            }
        }

        if (array_key_exists('dayOfWeek', $data)) {
            if (is_array($data['dayOfWeek']) && array_key_exists('uuid', $data['dayOfWeek'])) {
                $object->setDayOfWeek(
                    $this->entityManager->getRepository(DayOfWeek::class)->findOneBy(
                        ['uuid' => $data['dayOfWeek']['uuid']]
                    )
                );
            } else {
                $object->setDayOfWeek(null);
            }
        }

        return $object;
    }
}
