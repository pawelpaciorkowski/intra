<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Index;
use App\Repository\Traits\QueryBuilderExtension;
use App\Services\Component\ParameterBagInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class IndexRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Index::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['i'])
            ->from(Index::class, 'i');

        return $this
            ->includeFilterQueryData(
                [
                    'query' => ['i.objectData'],
                ]
            )
            ->order('i.priority')
            ->return();
    }

}
