<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class EmailEvent extends Event
{
    public function __construct(
        protected string $trigger,
        protected array $dataForTemplate
    ) {
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function getDataForTemplate(?string $key = null)
    {
        if ($key) {
            return $this->dataForTemplate[$key];
        }

        return $this->dataForTemplate;
    }
}
