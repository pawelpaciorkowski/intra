<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

interface DataPersisterInterface
{
    public function persist(array $data): object;

    public function update(array $data): object;

    public function remove(array $data): void;

    public function isSupported(array $data): bool;
}
