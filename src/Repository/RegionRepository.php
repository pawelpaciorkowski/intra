<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Region;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class RegionRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Region::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['r'])
            ->from(Region::class, 'r');

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['r.name'],
                    'query' => ['r.name'],
                ]
            )
            ->where(
                ['region-id' => 'r.id', 'id' => 'r.id']
            )
            ->order('r.name')
            ->return();
    }
}
