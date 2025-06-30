<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\CustomerService as CustomerServiceEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\CustomerService as CustomerServiceMapper;
use Doctrine\ORM\EntityManagerInterface;

class CustomerService implements DataPersisterInterface
{
    private $entityManager;
    private $customerServiceMapper;

    public function __construct(EntityManagerInterface $entityManager, CustomerServiceMapper $customerServiceMapper)
    {
        $this->entityManager = $entityManager;
        $this->customerServiceMapper = $customerServiceMapper;
    }

    public function persist(array $data): object
    {
        $customerService = $this->customerServiceMapper->populateData(new CustomerServiceEntity(), $data);

        $this->entityManager->persist($customerService);
        $this->entityManager->flush();

        return $customerService;
    }

    public function update(array $data): object
    {
        $customerService = $this->entityManager->getRepository(CustomerServiceEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$customerService) {
            throw new DataPersisterException(sprintf('CustomerService with UUID %s not found', $data['uuid']));
        }

        $this->customerServiceMapper->populateData($customerService, $data);
        $this->entityManager->flush();

        return $customerService;
    }

    public function remove(array $data): void
    {
        $customerService = $this->entityManager->getRepository(CustomerServiceEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$customerService) {
            throw new DataPersisterException(sprintf('CustomerService with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($customerService);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
