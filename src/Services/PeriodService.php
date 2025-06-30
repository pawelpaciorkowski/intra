<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\CollectionPoint;
use App\Entity\DayOfWeek;
use App\Entity\Period;
use App\Services\Exception\ParseException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class PeriodService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @throws ParseException
     */
    public function setPeriodsForCollectionPointAndDay(
        ?string $times,
        string $type,
        CollectionPoint $collectionPoint,
        DayOfWeek $dayOfWeek
    ): void {
        $existingPeriods = $this->getExistingPeriodsForCollectionPointAndDay($collectionPoint, $dayOfWeek, $type);

        if ($times) {
            $times = strtolower($times);

            if (!str_contains($times, 'zamk') && ('nieczynny' !== $times)) {
                foreach (explode(',', $times) as $time) {
                    [$start, $end] = $this->parseTime($time);

                    if ($period = array_shift($existingPeriods)) {
                        $period
                            ->setStartAt($start)
                            ->setEndAt($end);
                    } else {
                        $period = new Period();
                        $period
                            ->setCollectionPoint($collectionPoint)
                            ->setDayOfWeek($dayOfWeek)
                            ->setStartAt($start)
                            ->setEndAt($end)
                            ->setType($type);

                        $this->entityManager->persist($period);
                    }
                }
            }
        }

        foreach ($existingPeriods as $period) {
            $this->entityManager->remove($period);
        }
    }

    public function getExistingPeriodsForCollectionPointAndDay(
        CollectionPoint $collectionPoint,
        DayOfWeek $day,
        string $type = Period::TYPE_WORK
    ): array {
        return $this->entityManager->getRepository(Period::class)->findBy([
            'collectionPoint' => $collectionPoint,
            'dayOfWeek' => $day,
            'type' => $type,
        ]);
    }

    public function parseTime($time): array
    {
        $time = trim($time, ' ');
        $time = str_replace(['.', ' '], [':', ''], $time);

        if (str_starts_with($time, 'ca')) {
            $start = new DateTime('00:00');
            $end = new DateTime('00:00');
        } elseif (preg_match('/^([0-9]{1,2}:[0-9]{1,2})-([0-9]{1,2}:[0-9]{1,2})$/', $time, $match)) {
            $start = new DateTime($match[1]);
            $end = new DateTime($match[2]);
        } else {
            throw new ParseException(sprintf('Wrong time format \'%s\'', $time));
        }

        return [$start, $end];
    }
}
