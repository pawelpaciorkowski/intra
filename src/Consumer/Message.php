<?php

declare(strict_types=1);

namespace App\Consumer;

class Message
{
    private string $event;
    private string $producer;
    private string $entity;
    private array $data;

    public static function createFromArray(array $data): Message
    {
        $message = new self();
        $message
            ->setEvent($data['event'])
            ->setProducer($data['producer'])
            ->setEntity($data['entity'])
            ->setData($data['data']);

        return $message;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function setEvent(string $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getProducer(): string
    {
        return $this->producer;
    }

    public function setProducer(string $producer): self
    {
        $this->producer = $producer;

        return $this;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
