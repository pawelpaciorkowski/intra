<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Box;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class BoxRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Box::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['b', 'l', 'p'])
            ->from(Box::class, 'b')
            ->leftJoin('b.link', 'l')
            ->leftJoin('b.page', 'p');

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['b.title'],
                    'date' => ['b.date'],
                    'shortText' => ['b.shortText'],
                    'query' => ['b.title', 'b.date', 'b.shortText'],
                ]
            )
            ->where(['is-active' => 'b.isActive', 'id' => 'b.id'])
            ->order('b.sort')
            ->return();
    }
}
