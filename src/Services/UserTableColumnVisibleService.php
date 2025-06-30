<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\UserTableColumnVisible;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Component\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use function array_key_exists;

final class UserTableColumnVisibleService
{
    private $column;
    private $entityManager;
    private $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->column = [];
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function isVisible(string $table, string $column)
    {
        if (!array_key_exists($table, $this->column)) {
            $this->load($table);
        }

        if (!array_key_exists($column, $this->column[$table])) {
            return -1;
        }

        return $this->column[$table][$column];
    }

    private function load(string $table): void
    {
        $this->column[$table] = [];

        $rows = $this->entityManager
            ->getRepository(UserTableColumnVisible::class)
            ->findAllByParams(
                new ParameterBag([
                    'tableName' => $table,
                    'user' => $this->tokenStorage->getToken()?->getUser(),
                ])
            );

        foreach ($rows as $row) {
            $this->column[$table][$row->getTableColumn()->getColumn()] = $row->getIsVisible();
        }
    }
}
