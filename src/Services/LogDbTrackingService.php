<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\LogDbTracking;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Component\ParameterBagInterface;

final class LogDbTrackingService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        return $this->entityManager->getRepository(LogDbTracking::class)->findAllByParams($parameterBag);
    }
}
