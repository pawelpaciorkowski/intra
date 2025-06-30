<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Services\SettingService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class SettingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private RequestStack $requestStack,
        private SettingService $settingService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['parseSettings'],
        ];
    }

    public function parseSettings(): void
    {
        $params = $this->requestStack->getCurrentRequest()->query->all();

        $settings = [];
        foreach ($params as $key => $param) {
            if (preg_match('/^setting_(.*)$/', (string)$key, $match)) {
                $settings[$match[1]] = $param;
            }
        }

        if ($settings) {
            $this->settingService->saveSettings($settings, $this->tokenStorage->getToken()->getUser());
        }
    }
}
