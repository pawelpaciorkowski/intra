<?php

declare(strict_types=1);

namespace App\Entity;

interface IndexInterface
{
    public function getIsIndex(): bool;
    public function getDataForIndex(): array;
    public function getNameForIndex(): string;
    public function getDescriptionForIndex(): ?string;
    public function getPriority(): int;
}
