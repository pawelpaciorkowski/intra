<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister\Mapper;

use App\Entity\CollectionPoint;
use App\Entity\CustomerService;
use App\Entity\Laboratory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class Phone implements ConsumerMapperInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function populateData(object $object, array $data): object
    {
        $object->setUuid($data['uuid']);

        if (array_key_exists('number', $data)) {
            $object->setNumber($data['number']);
        }

        if (array_key_exists('isVisible', $data)) {
            $object->setIsVisible((bool)$data['isVisible']);
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

        if (array_key_exists('user', $data)) {
            if (is_array($data['user']) && array_key_exists('uuid', $data['user'])) {
                $object->setUser(
                    $this->entityManager->getRepository(User::class)->findOneBy(['uuid' => $data['user']['uuid']])
                );
            } else {
                $object->setUser(null);
            }
        }

        if (array_key_exists('customerService', $data)) {
            if (is_array($data['customerService']) && array_key_exists('uuid', $data['customerService'])) {
                $object->setCustomerService(
                    $this->entityManager->getRepository(CustomerService::class)->findOneBy(
                        ['uuid' => $data['customerService']['uuid']]
                    )
                );
            } else {
                $object->setCustomerService(null);
            }
        }

        if (array_key_exists('laboratory', $data)) {
            if (is_array($data['laboratory']) && array_key_exists('uuid', $data['laboratory'])) {
                $object->setLaboratory(
                    $this->entityManager->getRepository(Laboratory::class)->findOneBy(
                        ['uuid' => $data['laboratory']['uuid']]
                    )
                );
            } else {
                $object->setLaboratory(null);
            }
        }

        return $object;
    }
}
