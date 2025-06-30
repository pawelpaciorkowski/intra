<?php

declare(strict_types=1);

namespace App\Logging;

use JsonException;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;

use function is_array;
use function is_object;
use function json_encode;

abstract class BaseLogger
{
    protected IntrospectionProcessor $processor;

    public function __construct(protected Logger $logger)
    {
        $this->processor = new IntrospectionProcessor();
    }

    /**
     * @throws JsonException
     */
    public function logError(string $message, array $context = []): bool
    {
        $this->logger->pushProcessor($this->processor);

        return $this->log(Level::Error, $message, $context);
    }

    /**
     * @throws JsonException
     */
    protected function log(Level $level, mixed $message, array $context): bool
    {
        if (is_array($message) || is_object($message)) {
            $message = json_encode($message, JSON_THROW_ON_ERROR);
        }

        return $this->logger->addRecord($level, (string)$message, $context);
    }

    /**
     * @throws JsonException
     */
    public function logWarning(string $message, array $context = []): bool
    {
        return $this->log(Level::Warning, $message, $context);
    }

    /**
     * @throws JsonException
     */
    public function logNotice(string $message, array $context = []): bool
    {
        return $this->log(Level::Notice, $message, $context);
    }

    /**
     * @throws JsonException
     */
    public function logInfo(string $message, array $context = []): bool
    {
        return $this->log(Level::Info, $message, $context);
    }
}
