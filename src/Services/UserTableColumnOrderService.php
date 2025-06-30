<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\TableColumn;
use App\Entity\UserTableColumnOrder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function array_key_exists;
use function array_walk;
use function explode;
use function implode;

final class UserTableColumnOrderService
{
    private $tableOrder = [];

    private $entityManager;
    private $tokenStorage;
    private $authorizationChecker;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function findOrderForTable(string $tableName)
    {
        if (array_key_exists($tableName, $this->tableOrder)) {
            return $this->tableOrder[$tableName];
        }

        $user = $this->authorizationChecker->isGranted(
            'IS_AUTHENTICATED_REMEMBERED'
        ) ? $this->tokenStorage->getToken()->getUser() : null;

        if ($this->tableOrder[$tableName] = $this->entityManager
            ->getRepository(UserTableColumnOrder::class)
            ->findOrderForTableWithUser($user, $tableName)
        ) {
            $this->tableOrder[$tableName]['orderBy'] = $this->getOrderBy(
                $this->tableOrder[$tableName]['column'],
                $this->tableOrder[$tableName]['direction']
            );
        }

        return $this->tableOrder[$tableName];
    }

    private function getOrderBy(TableColumn $column, bool $isDirection): string
    {
        $columns = explode(',', $column->getColumnRealSort());
        array_walk(
            $columns,
            static function (&$value) use ($isDirection): void {
                $value .= ' ' . ($isDirection ? 'DESC' : 'ASC');
            }
        );

        return implode(',', $columns);
    }

    public function setOrderForTable(string $tableName, string $columnName, bool $isDirection): self
    {
        // search for column definition
        $column = $this->entityManager->getRepository(TableColumn::class)->findTableColumn($tableName, $columnName);

        if (!$column) {
            throw new EntityNotFoundException('App\Entity\TableColumn not found');
        }

        // search for user selected order
        $userTableOrder = $this
            ->entityManager
            ->getRepository(UserTableColumnOrder::class)
            ->findUserTableColumnOrder($this->tokenStorage->getToken()->getUser(), $tableName);

        // if user has custom order for this table
        if ($userTableOrder) {
            // order changed
            if ($column !== $userTableOrder->getTableColumn() || $userTableOrder->getIsDirection() !== $isDirection) {
                $userTableOrder->setTableColumn($column)->setIsDirection($isDirection);
            }
            // if user doesn't have custom order for this table, create one
        } else {
            $userTableOrder = new UserTableColumnOrder();
            $userTableOrder->setTableColumn($column)->setUser(
                $this->tokenStorage
                    ->getToken()
                    ->getUser()
            )->setIsDirection($column->getIsDefaultSort() && !$column->getTable()->getIsDirection());
        }

        $this->entityManager->persist($userTableOrder);
        $this->entityManager->flush();

        $this->tableOrder[$tableName] = ['column' => $column, 'direction' => $userTableOrder->getIsDirection()];
        $this->tableOrder[$tableName]['orderBy'] = $this->getOrderBy(
            $this->tableOrder[$tableName]['column'],
            $this->tableOrder[$tableName]['direction']
        );

        return $this;
    }
}
