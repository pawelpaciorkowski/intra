<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\BillingCenter as BillingCenterEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\BillingCenter as BillingCenterMapper;
use Doctrine\ORM\EntityManagerInterface;

class BillingCenter implements DataPersisterInterface
{
    private $entityManager;
    private $billingCenterMapper;

    public function __construct(EntityManagerInterface $entityManager, BillingCenterMapper $billingCenterMapper)
    {
        $this->entityManager = $entityManager;
        $this->billingCenterMapper = $billingCenterMapper;
    }

    public function persist(array $data): object
    {
        $billingCenter = $this->billingCenterMapper->populateData(new BillingCenterEntity(), $data);

        $this->entityManager->persist($billingCenter);
        $this->entityManager->flush();

        return $billingCenter;
    }

    /**
     * @throws DataPersisterException
     */
    public function update(array $data): object
    {
        $billingCenter = $this->entityManager->getRepository(BillingCenterEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$billingCenter) {
            throw new DataPersisterException(sprintf('BillingCenter with UUID %s not found', $data['uuid']));
        }

        $this->billingCenterMapper->populateData($billingCenter, $data);
        $this->entityManager->flush();

        return $billingCenter;
    }

    /**
     * @throws DataPersisterException
     */
    public function remove(array $data): void
    {
        $billingCenter = $this->entityManager->getRepository(BillingCenterEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$billingCenter) {
            throw new DataPersisterException(sprintf('BillingCenter with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($billingCenter);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
