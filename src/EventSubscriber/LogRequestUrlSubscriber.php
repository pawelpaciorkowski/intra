<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Services\LogRequestService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class LogRequestUrlSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $entityManager,
        private readonly LogRequestService $logRequestService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 30],
            KernelEvents::CONTROLLER => ['onCoreController'],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernel::MAIN_REQUEST === $event->getRequestType()) {
            $this->logRequestService->setLogRequest(
                $this->logRequestService->saveRequest($this->requestStack->getCurrentRequest())
            );

            $this->entityManager->flush();
        }
    }

    public function onCoreController(ControllerEvent $event): void
    {
        if ((HttpKernel::MAIN_REQUEST === $event->getRequestType()) && $this->logRequestService->getLogRequest()) {
            $user = null;
            if ($this->tokenStorage->getToken()) {
                $user = $this->tokenStorage->getToken()->getUser();
            }

            $this->logRequestService->getLogRequest()->setUser($user);
            $this->entityManager->flush();
        }
    }

}
