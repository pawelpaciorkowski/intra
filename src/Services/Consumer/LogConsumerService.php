<?php

declare(strict_types=1);

namespace App\Services\Consumer;

use App\Entity\LogConsumer;
use Doctrine\ORM\EntityManagerInterface;

class LogConsumerService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function log(string $data, bool $isSuccess = true, ?string $exception = null): LogConsumer
    {
        $logConsumer = new LogConsumer();
        $logConsumer
            ->setBody($data)
            ->setIsSuccess($isSuccess)
            ->setException($exception);

        $this->entityManager->persist($logConsumer);
        $this->entityManager->flush();

        return $logConsumer;
    }
}
