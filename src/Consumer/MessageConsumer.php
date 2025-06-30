<?php

declare(strict_types=1);

namespace App\Consumer;

use App\Services\Consumer\ConsumerService;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\Exception\ConsumerException;
use App\Services\Consumer\LogConsumerService;
use JsonException;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

final class MessageConsumer implements ConsumerInterface
{
    public function __construct(public ConsumerService $consumerService, public LogConsumerService $logConsumerService)
    {
    }

    /**
     * @throws JsonException
     */
    public function execute(AMQPMessage $msg): int
    {
        $message = Message::createFromArray(json_decode($msg->getBody(), true, 512, JSON_THROW_ON_ERROR));

        try {
            $this->consumerService->consume($message);
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ConsumerException | DataPersisterException $e) {
            $this->logConsumerService->log($msg->getBody(), false, $e->getMessage());

            return ConsumerInterface::MSG_REJECT;
        }

        $this->logConsumerService->log($msg->getBody());

        return ConsumerInterface::MSG_ACK;
    }
}
