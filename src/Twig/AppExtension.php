<?php

declare(strict_types=1);

namespace App\Twig;

use App\Services\UserTableColumnOrderService;
use App\Services\UserTableColumnVisibleService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class AppExtension extends AbstractExtension
{
    public function __construct(
        private readonly UserTableColumnOrderService $userTableColumnOrderService,
        private readonly UserTableColumnVisibleService $userTableColumnVisibleService
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'expand',
                [new TextExtension(), 'expand'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFilter(
                'beautify',
                [new DateExtension(), 'beautify'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFilter(
                'json_beautify',
                [new DateExtension(), 'jsonBeautify'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFilter(
                'camel2dashed',
                [new TextExtension(), 'camel2dashed'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'user_table_column_visible',
                [
                    new ColumnExtension($this->userTableColumnOrderService, $this->userTableColumnVisibleService),
                    'userTableColumnVisible',
                ],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'user_table_column_order',
                [
                    new ColumnExtension($this->userTableColumnOrderService, $this->userTableColumnVisibleService),
                    'userTableColumnOrder',
                ],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'simple_column_order',
                [
                    new SimpleColumnExtension(),
                    'simpleColumnOrder',
                ],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'js_column_order',
                [
                    new SimpleColumnExtension(),
                    'jsColumnOrder',
                ],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        ];
    }
}
