<?php

declare(strict_types=1);

namespace App\Services\FullCalendar;

use DateTimeInterface;

class Event
{
    private $id;
    private $display;
    private $color;
    private $classNames;
    private $isOverlap;
    private $start;
    private $end;
    private $title;
    private $extendedProps;

    public function __construct()
    {
        $this->display = '';
        $this->isOverlap = false;
        $this->classNames = [];
        $this->extendedProps = [];
    }

    public function getDisplay(): string
    {
        return $this->display;
    }

    public function setDisplay(string $display): self
    {
        $this->display = $display;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getClassNames(): array
    {
        return $this->classNames;
    }

    public function setClassNames(array $classNames): self
    {
        $this->classNames = $classNames;

        return $this;
    }

    public function addClassNames(string $className): self
    {
        $this->classNames[] = $className;

        return $this;
    }

    public function getIsOverlap(): bool
    {
        return $this->isOverlap;
    }

    public function setIsOverlap(bool $isOverlap): self
    {
        $this->isOverlap = $isOverlap;

        return $this;
    }

    public function getStart(): DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getExtendedProps(): array
    {
        return $this->extendedProps;
    }

    public function setExtendedProps(array $extendedProps): self
    {
        $this->extendedProps = $extendedProps;

        return $this;
    }

    public function addExtendedProps($key, array $value): self
    {
        $this->extendedProps[$key] = $value;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
