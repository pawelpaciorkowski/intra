<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TableColumn;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class TableColumnRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TableColumn::class);
    }

    public function findTableColumn(string $tableName, string $columnName): ?TableColumn
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['tc'])
            ->from(TableColumn::class, 'tc')
            ->join('tc.table', 't')
            ->andWhere('t.table = :tableName')
            ->setParameter('tableName', $tableName)
            ->andWhere('tc.column = :columnName')
            ->setParameter('columnName', $columnName)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
