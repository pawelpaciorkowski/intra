<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;

use function array_key_exists;
use function array_merge;

final class RequestService
{
    private $requestStack;
    private $userTableColumnOrderService;

    private $query;

    public function __construct(RequestStack $requestStack, UserTableColumnOrderService $userTableColumnOrderService)
    {
        $this->requestStack = $requestStack;
        $this->userTableColumnOrderService = $userTableColumnOrderService;

        $this->query = $this->requestStack->getCurrentRequest()->query->all();
    }

    public function getQuery(string $key = null)
    {
        if ($key) {
            if (array_key_exists($key, $this->query)) {
                return $this->query[$key];
            }

            return null;
        }

        return $this->query;
    }

    public function sortHandle(?string $tableName = null): self
    {
        if (!$tableName) {
            $tableName = $this->requestStack->getCurrentRequest()->get('_route');
        }

        if (array_key_exists('sort', $this->query)) {
            $this->userTableColumnOrderService->setOrderForTable(
                $tableName,
                $this->query['sort'],
                (bool)$this->query['direction']
            );
        }

        $userTableColumnOrder = $this->userTableColumnOrderService->findOrderForTable($tableName);

        if ($userTableColumnOrder) {
            $this->query = array_merge($this->query, $userTableColumnOrder);
        }

        return $this;
    }
}
