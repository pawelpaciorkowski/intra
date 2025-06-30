<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Services\SecurityService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class SecuritySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private AuthorizationCheckerInterface $authorizationChecker,
        private SecurityService $securityService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController'],
        ];
    }

    public function onKernelController(ControllerEvent $event): bool
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return false;
        }

        // Don't check auth if user in unauthenticated
        if (!$this->tokenStorage->getToken() || false === $this->authorizationChecker->isGranted(
            'IS_AUTHENTICATED_REMEMBERED'
        )
        ) {
            return false;
        }

        $routeName = $event->getRequest()->get('_route');

        if (!$this->securityService->hasAccess($routeName)) {
            throw new AccessDeniedException($routeName);
        }

        return true;
    }
}
