<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Region;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Component\ParameterBagInterface;

final class RegionService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createOrUpdate(string $name): ?Region
    {
        if ('' === $name) {
            return null;
        }

        $name = preg_replace('/^Region\s+/', '', $name);

        $region = $this->entityManager->getRepository(Region::class)->findOneByName($name);
        if (!$region) {
            $region = new Region();
        }

        $region->setName($name);
        $this->entityManager->persist($region);

        return $region;
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        return $this->entityManager->getRepository(Region::class)->findAllByParams($parameterBag);
    }
}
