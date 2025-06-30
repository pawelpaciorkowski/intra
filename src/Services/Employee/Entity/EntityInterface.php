<?php

declare(strict_types=1);

namespace App\Services\Employee\Entity;

interface EntityInterface
{
    public static function fromArray(array $data): static;
}
