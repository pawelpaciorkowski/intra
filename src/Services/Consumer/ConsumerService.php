<?php

declare(strict_types=1);

namespace App\Services\Consumer;

use App\Consumer\Message;
use App\Entity\BillingCenter;
use App\Entity\Calendar;
use App\Entity\ChatbotizeCalendar;
use App\Entity\City;
use App\Entity\CollectionPoint;
use App\Entity\CollectionPointClassification;
use App\Entity\CollectionPointCloseDate;
use App\Entity\CollectionPointExtraDate;
use App\Entity\CollectionPointLocation;
use App\Entity\CollectionPointPartner;
use App\Entity\CollectionPointType;
use App\Entity\Country;
use App\Entity\CustomerService;
use App\Entity\Database;
use App\Entity\Lab;
use App\Entity\LabGroup;
use App\Entity\Laboratory;
use App\Entity\Period;
use App\Entity\Phone;
use App\Entity\Position;
use App\Entity\Province;
use App\Entity\Region;
use App\Entity\Team;
use App\Entity\User;
use App\Services\Consumer\Exception\ConsumerException;
use Doctrine\ORM\EntityManagerInterface;

class ConsumerService
{
    private const ENTITY = [
        'BillingCenter' => BillingCenter::class,
        'Calendar' => Calendar::class,
        'ChatbotizeCalendar' => ChatbotizeCalendar::class,
        'City' => City::class,
        'CollectionPoint' => CollectionPoint::class,
        'CollectionPointClassification' => CollectionPointClassification::class,
        'CollectionPointLocation' => CollectionPointLocation::class,
        'CollectionPointPartner' => CollectionPointPartner::class,
        'CollectionPointType' => CollectionPointType::class,
        'CollectionPointCloseDate' => CollectionPointCloseDate::class,
        'CollectionPointExtraDate' => CollectionPointExtraDate::class,
        'Country' => Country::class,
        'CustomerService' => CustomerService::class,
        'Database' => Database::class,
        'Lab' => Lab::class,
        'LabGroup' => LabGroup::class,
        'Laboratory' => Laboratory::class,
        'Period' => Period::class,
        'Phone' => Phone::class,
        'Province' => Province::class,
        'Region' => Region::class,
        'Team' => Team::class,
        'Position' => Position::class,
        'User' => User::class,
    ];

    private EntityManagerInterface $entityManager;
    private string $appName;

    public function __construct(EntityManagerInterface $entityManager, string $appName)
    {
        $this->entityManager = $entityManager;
        $this->appName = $appName;
    }

    public function consume(Message $data): void
    {
        if (
            $this->isProducerSupported($data->getProducer())
            && $this->isEventSupported($data->getEvent())
            && $this->isEntitySupported($data->getEntity())
        ) {
            $dataMapperName = sprintf('\App\Services\Consumer\DataPersister\Mapper\%s', $data->getEntity());
            $dataMapper = new $dataMapperName($this->entityManager);

            $dataPersisterName = sprintf('\App\Services\Consumer\DataPersister\%s', $data->getEntity());
            $dataPersister = new $dataPersisterName($this->entityManager, $dataMapper);

            $dataPersister->{$data->getEvent()}($data->getData());
        }
    }

    /**
     * @throws ConsumerException
     */
    public function isProducerSupported(string $producer): bool
    {
        if ($producer === $this->appName) {
            throw new ConsumerException('Message has improper producer name');
        }

        return true;
    }

    /**
     * @throws ConsumerException
     */
    public function isEventSupported(string $event): bool
    {
        if (!in_array($event, ['persist', 'remove', 'update'], true)) {
            throw new ConsumerException('Message has improper event name');
        }

        return true;
    }

    /**
     * @throws ConsumerException
     */
    public function isEntitySupported(string $entity): bool
    {
        if (!array_key_exists($entity, self::ENTITY)) {
            throw new ConsumerException('Message has improper entity name');
        }

        return true;
    }
}
