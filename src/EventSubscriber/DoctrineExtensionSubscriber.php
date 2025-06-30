<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Gedmo\Loggable\LoggableListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class DoctrineExtensionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private AuthorizationCheckerInterface $authorizationChecker,
        private LoggableListener $loggableListener
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }

    public function onKernelRequest(): void
    {
        if ($this->tokenStorage->getToken() && false !== $this->authorizationChecker->isGranted(
            'IS_AUTHENTICATED_REMEMBERED'
        )
        ) {
            $this->loggableListener->setUsername($this->tokenStorage->getToken()->getUser()->getFullname());
        }
    }
}
