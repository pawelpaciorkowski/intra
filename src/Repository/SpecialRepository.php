<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Special;
use App\Repository\Traits\QueryBuilderExtension;
use App\Services\Component\ParameterBagInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class SpecialRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Special::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['s'])
            ->from(Special::class, 's');

        return $this
            ->includeFilterQueryData(
                [
                    'id' => ['s.id'],
                    'title' => ['s.title'],
                    'long' => ['s.long'],
                    'query' => ['s.title', 's.longText'],
                ]
            )
            ->where(['id' => 's.id'])
            ->order('s.title')
            ->return();
    }
}
