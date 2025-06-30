<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Link;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class LinkRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['l', 'r'])
            ->from(Link::class, 'l')
            ->leftJoin('l.roles', 'r');

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['l.name', 'r.name'],
                    'query' => ['l.name', 'r.name'],
                ]
            )
            ->where([
                'isDone' => 'l.isDone',
                'routeName' => 'l.name',
                'id' => 'l.id'
            ])
            ->order('l.name')
            ->return();
    }

    public function findLinkWithRole(): array
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['l', 'r'])
            ->from(Link::class, 'l')
            ->leftJoin('l.roles', 'r')
            ->getQuery()
            ->getResult();
    }
}
