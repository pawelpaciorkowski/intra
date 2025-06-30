<?php

declare(strict_types=1);

namespace App\Services;

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

final readonly class PsajdakService
{
    public function __construct(public ProducerInterface $producer)
    {
    }

    public function publish(array $message): void
    {
        $this->producer->publish(
            json_encode($message, JSON_THROW_ON_ERROR),
            'app.name:psajdak',
            [
                'content_type' => 'application/json',
                'type' => 'mail',
                'app_id' => 'app.name:intranet',
            ]
        );
    }
}
