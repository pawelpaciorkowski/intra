<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister\Mapper;

use App\Entity\CollectionPoint;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class CollectionPointExtraDate implements ConsumerMapperInterface
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

        if (array_key_exists('comment', $data)) {
            $object->setComment($data['comment']);
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

        return $object;
    }
}
