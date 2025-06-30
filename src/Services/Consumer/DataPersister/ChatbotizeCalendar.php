<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister;

use App\Entity\ChatbotizeCalendar as ChatbotizeCalendarEntity;
use App\Services\Consumer\DataPersister\Exception\DataPersisterException;
use App\Services\Consumer\DataPersister\Mapper\ChatbotizeCalendar as ChatbotizeCalendarMapper;
use Doctrine\ORM\EntityManagerInterface;

class ChatbotizeCalendar implements DataPersisterInterface
{
    private $entityManager;
    private $chatbotizeCalendarMapper;

    public function __construct(
        EntityManagerInterface $entityManager,
        ChatbotizeCalendarMapper $chatbotizeCalendarMapper
    ) {
        $this->entityManager = $entityManager;
        $this->chatbotizeCalendarMapper = $chatbotizeCalendarMapper;
    }

    public function persist(array $data): object
    {
        $chatbotizeCalendar = $this->chatbotizeCalendarMapper->populateData(new ChatbotizeCalendarEntity(), $data);

        $this->entityManager->persist($chatbotizeCalendar);
        $this->entityManager->flush();

        return $chatbotizeCalendar;
    }

    /**
     * @throws DataPersisterException
     */
    public function update(array $data): object
    {
        $chatbotizeCalendar = $this->entityManager->getRepository(ChatbotizeCalendarEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$chatbotizeCalendar) {
            throw new DataPersisterException(sprintf('ChatbotizeCalendar with UUID %s not found', $data['uuid']));
        }

        $this->chatbotizeCalendarMapper->populateData($chatbotizeCalendar, $data);
        $this->entityManager->flush();

        return $chatbotizeCalendar;
    }

    /**
     * @throws DataPersisterException
     */
    public function remove(array $data): void
    {
        $chatbotizeCalendar = $this->entityManager->getRepository(ChatbotizeCalendarEntity::class)->findOneBy(
            ['uuid' => $data['uuid']]
        );

        if (!$chatbotizeCalendar) {
            throw new DataPersisterException(sprintf('ChatbotizeCalendar with UUID %s not found', $data['uuid']));
        }

        $this->entityManager->remove($chatbotizeCalendar);
        $this->entityManager->flush();
    }

    public function isSupported(array $data): bool
    {
        return true;
    }
}
