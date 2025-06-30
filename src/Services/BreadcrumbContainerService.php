<?php

declare(strict_types=1);

namespace App\Services;

final class BreadcrumbContainerService
{
    private array $name;

    public function __construct()
    {
        $this->name = [];
    }

    public function all(): array
    {
        return $this->name;
    }

    public function add(string $name): void
    {
        $this->name[] = $name;
    }
}
