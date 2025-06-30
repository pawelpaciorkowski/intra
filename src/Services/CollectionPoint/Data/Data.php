<?php

declare(strict_types=1);

namespace App\Services\CollectionPoint\Data;

use Countable;
use Iterator;

class Data implements DataInterface, Iterator, Countable
{
    private array $rows;

    public function addRow(Row $row): self
    {
        $this->rows[] = $row;

        return $this;
    }

    public function getRows(): array
    {
        return $this->rows;
    }

    public function current(): mixed
    {
        return current($this->rows);
    }

    public function next(): mixed
    {
        return next($this->rows);
    }

    public function key(): mixed
    {
        return key($this->rows);
    }

    public function valid(): bool
    {
        $key = key($this->rows);

        return null !== $key && false !== $key;
    }

    public function rewind(): void
    {
        reset($this->rows);
    }

    public function count(): int
    {
        return count($this->rows);
    }
}
