<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\CollectionPoint;
use App\Services\FullCalendar\Event;
use DateInterval;
use DateTime;
use App\Services\Component\ParameterBag;

final class CalendarService
{
    private $dayService;

    public function __construct(DayService $dayService)
    {
        $this->dayService = $dayService;
    }

    public function getPeriodsForView(CollectionPoint $collectionPoint, DateTime $startAt, DateTime $endAt): array
    {
        $current = clone $startAt;

        $periods = [];

        // daty otwarcia i pobrań
        while ($current < $endAt) {
            foreach ($collectionPoint->getPeriods() as $period) {
                if ((int)$current->format('N') === $period->getDayOfWeek()->getId()) {
                    $event = new Event();
                    $event
                        ->setDisplay('background')
                        ->addClassNames($period->getType());

                    $date = clone $current;
                    if ($period->getIsAllDay()) {
                        $event
                            ->setStart($date->setTime(0, 0))
                            ->setEnd((clone $date)->add(new DateInterval('P1D')))
                            ->setTitle('00:00 - 00:00');
                    } else {
                        $event
                            ->setStart(
                                $date->setTime(
                                    (int)$period->getStartAt()->format('H'),
                                    (int)$period->getStartAt()->format('i')
                                )
                            )
                            ->setEnd(
                                (clone $date)->setTime(
                                    (int)$period->getEndAt()->format('H'),
                                    (int)$period->getEndAt()->format('i')
                                )
                            )
                            ->setTitle(
                                sprintf(
                                    '%s - %s',
                                    $period->getStartAt()->format('H:i'),
                                    $period->getEndAt()->format('H:i')
                                )
                            );
                    }

                    $periods[] = $event;
                }
            }

            $current->add(new DateInterval('P1D'));
        }

        // Daty zamknięcia
        foreach ($collectionPoint->getCollectionPointCloseDates() as $closeDate) {
            $start = $closeDate->getStartAt();
            $end = $closeDate->getEndAt();

            if ($start <= $endAt && $end >= $startAt) {
                $periods[] = (new Event())
                    ->setDisplay('background')
                    ->setTitle($closeDate->getComment())
                    ->addClassNames('closed')
                    ->setStart($start)
                    ->setEnd($end);
            }
        }

        // Dodatkowa data otwarcia
        foreach ($collectionPoint->getCollectionPointExtraDates() as $extraDate) {
            $start = $extraDate->getStartAt();
            $end = $extraDate->getEndAt();

            if ($start <= $endAt && $end >= $startAt) {
                $periods[] = (new Event())
                    ->setDisplay('background')
                    ->setTitle($extraDate->getComment())
                    ->addClassNames('extra')
                    ->setStart($start)
                    ->setEnd($end);
            }
        }

        // Dni wolne
        $days = $this->dayService->findAllByParams(new ParameterBag([
            'startAt' => $startAt,
            'endAt' => $endAt,
        ]));
        foreach ($days as $day) {
            $start = $day->getDate();
            $end = (clone $day->getDate())->setTime(0, 0)->add(new DateInterval('P1D'));

            if ($start <= $endAt && $end >= $startAt) {
                $periods[] = (new Event())
                    ->setDisplay('background')
                    ->setTitle($day->getName())
                    ->addClassNames('closed')
                    ->setStart($start)
                    ->setEnd($end);
            }
        }

        return $periods;
    }
}
