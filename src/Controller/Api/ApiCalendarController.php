<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Services\CalendarService;
use App\Services\CollectionPointService;
use DateTime;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use App\Services\Component\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

final class ApiCalendarController extends AbstractFOSRestController
{
    /**
     * @throws \Exception
     */
    #[Get("/api/calendar/{id}", name: "api-get-calendar", requirements: ["id" => "\d+"])]
    public function getList(
        int $id,
        Request $request,
        CollectionPointService $collectionPointService,
        CalendarService $calendarService,
        SerializerInterface $serializer
    ): Response {
        $collectionPoint = $collectionPointService->findAllByParams(
            new ParameterBag([
                'id' => $id,
                'withPeriods' => true,
                'orderBy' => 'pe.id asc',
            ])
        );
        if (!$collectionPoint) {
            throw $this->createNotFoundException('App\Entity\CollectionPoint object not found.');
        }

        return new Response(
            $serializer->serialize(
                $calendarService->getPeriodsForView(
                    $collectionPoint,
                    new DateTime($request->query->get('start')),
                    new DateTime($request->query->get('end'))
                ),
                'json'
            )
        );
    }
}
