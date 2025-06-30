<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Table;
use App\Entity\UserTableColumnOrder;
use App\Repository\Exception\UserTableColumnOrderException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserTableColumnOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserTableColumnOrder::class);
    }

    public function findOrderForTableWithUser(?UserInterface $user, string $tableName): array
    {
        if ($user && $result = $this->findUserTableColumnOrder($user, $tableName)) {
            return [
                'column' => $result->getTableColumn(),
                'direction' => $result->getIsDirection(),
            ];
        }

        return $this->findOrderForTable($tableName);
    }

    public function findUserTableColumnOrder(UserInterface $user, string $tableName): ?UserTableColumnOrder
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select(['utco'])
            ->from(UserTableColumnOrder::class, 'utco')
            ->join('utco.tableColumn', 'tc')
            ->join('tc.table', 't')
            ->where('t.table = :tableName')
            ->setParameter('tableName', $tableName)
            ->andWhere('utco.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOrderForTable(string $tableName): array
    {
        $query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['t', 'tc'])
            ->from(Table::class, 't')
            ->join('t.tableColumns', 'tc')
            ->where('tc.isDefaultSort = :isDefault')
            ->setParameter('isDefault', true)
            ->andWhere('t.table = :table')
            ->setParameter('table', $tableName);

        $result = $query
            ->getQuery()
            ->getOneOrNullResult();

        if (!$result) {
            throw new UserTableColumnOrderException('No default order for table ' . $tableName);
        }

        return [
            'column' => $result->getTableColumns()[0],
            'direction' => $result->getIsDirection(),
        ];
    }
}
