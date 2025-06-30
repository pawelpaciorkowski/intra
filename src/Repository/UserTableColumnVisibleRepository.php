<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserTableColumnVisible;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class UserTableColumnVisibleRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserTableColumnVisible::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        return $this
            ->prepareQuery()
            ->getQuery()
            ->getResult();
    }

    private function prepareQuery(): QueryBuilder
    {
        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('utcv')
            ->from(UserTableColumnVisible::class, 'utcv')
            ->join('utcv.user', 'u')
            ->join('utcv.tableColumn', 'tc')
            ->join('tc.table', 't');

        $this->where(['tableName' => 't.table', 'columnName' => 'tc.column', 'id' => 'utcv.id', 'user' => 'utcv.user']);

        return $this->query;
    }

    public function findRowByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        return $this
            ->prepareQuery()
            ->getQuery()
            ->getOneOrNullResult();
    }
}
