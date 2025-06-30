<?php

declare(strict_types=1);

namespace App\Twig;

use App\Services\UserTableColumnOrderService;
use App\Services\UserTableColumnVisibleService;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

use function strcmp;

final class ColumnExtension
{
    private $userTableColumnOrderService;
    private $userTableColumnVisibleService;

    public function __construct(
        UserTableColumnOrderService $userTableColumnOrderService,
        UserTableColumnVisibleService $userTableColumnVisibleService
    ) {
        $this->userTableColumnOrderService = $userTableColumnOrderService;
        $this->userTableColumnVisibleService = $userTableColumnVisibleService;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function userTableColumnOrder(Environment $env, string $title, string $table, string $column): string
    {
        $order = $this->userTableColumnOrderService->findOrderForTable($table);

        $selected = false;
        $direction = false;

        if ($order && $column === $order['column']->getColumn()) {
            $selected = true;
            $direction = !$order['direction'];
        }

        return $env->render(
            'twig/user-table-column-order.html.twig',
            [
                'title' => $title,
                'column' => $column,
                'direction' => $direction,
                'selected' => $selected,
            ]
        );
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function userTableColumnVisible(
        Environment $env,
        string $tag,
        string $isVisibleByDefault,
        string $table,
        string $column
    ): string {
        $isVisibleStored = $this->userTableColumnVisibleService->isVisible($table, $column);

        $isVisible = $isVisibleStored;
        if (-1 === $isVisibleStored) {
            $isVisible = $isVisibleByDefault;
        }

        $checked = false;
        $hidden = false;

        if ($isVisible) {
            if (0 === strcmp($tag, 'checkbox')) {
                $checked = true;
            }
        } elseif (0 !== strcmp($tag, 'checkbox')) {
            $hidden = true;
        }

        return $env->render(
            'twig/user-table-column-visible.html.twig',
            [
                'column' => $column,
                'checked' => $checked,
                'hidden' => $hidden,
            ]
        );
    }
}
