<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister\Mapper;

use App\Entity\ChatbotizeCalendar;
use App\Entity\CollectionPoint;
use Doctrine\ORM\EntityManagerInterface;

class Calendar implements ConsumerMapperInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function populateData(object $object, array $data): object
    {
        $object->setUuid($data['uuid']);

        if (array_key_exists('instanceId', $data)) {
            $object->setInstanceId($data['instanceId']);
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

        if (array_key_exists('chatbotizeCalendar', $data)) {
            if (is_array($data['chatbotizeCalendar']) && array_key_exists('uuid', $data['chatbotizeCalendar'])) {
                $object->setChatbotizeCalendar(
                    $this->entityManager->getRepository(ChatbotizeCalendar::class)->findOneBy(
                        ['uuid' => $data['chatbotizeCalendar']['uuid']]
                    )
                );
            } else {
                $object->setChatbotizeCalendar(null);
            }
        }

        return $object;
    }
}
