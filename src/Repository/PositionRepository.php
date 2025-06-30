<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Position;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

class PositionRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Position::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['p'])
            ->from(Position::class, 'p');

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['p.name'],
                    'query' => ['p.name'],
                ]
            )
            ->where(['id' => 'p.id'])
            ->order('p.name')
            ->return();
    }
}
