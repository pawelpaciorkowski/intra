<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class LastRouteSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 30],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Do not save subrequests
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        $routeName = $request->get('_route');
        $routeParams = $request->get('_route_params');
        $queryParams = $request->query->all();
        if ('_' === $routeName[0] || str_starts_with((string)$routeName, 'api-')) {
            return;
        }
        $routeData = ['name' => $routeName, 'params' => array_merge($routeParams, $queryParams)];

        // Do not save same matched route twice
        $thisRoute = $session->get('this_route', []);
        if ($thisRoute === $routeData) {
            return;
        }
        $session->set('last_route', $thisRoute);
        $session->set('this_route', $routeData);
    }
}
