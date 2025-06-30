<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\LogUserAuth;
use App\Services\SettingService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\SecurityEvents;

final readonly class AuthenticationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage,
        private SettingService $settingService
    ) {
    }

    #[ArrayShape([SecurityEvents::INTERACTIVE_LOGIN => "string[]", LoginFailureEvent::class => "string[]"])]
    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => ['onInteractiveLogin'],
            LoginFailureEvent::class => ['onLoginFailure']
        ];
    }

    public function onInteractiveLogin(): void
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $user->setLastLoginAt(new DateTime());
        $this->entityManager->persist($user);

        $ip = $this->requestStack->getCurrentRequest()->getClientIp();

        $oLogUserAuth = new LogUserAuth();
        $oLogUserAuth
            ->setIp($ip)
            ->setBrowser(array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown')
            ->setUsername($this->requestStack->getCurrentRequest()->get('username'))
            ->setIsSuccess(true)
            ->setUser($user);

        $this->entityManager->persist($oLogUserAuth);
        $this->entityManager->flush();

        // Load user settings
        $this->settingService->reloadSettings($user);
    }

    public function onLoginFailure(): void
    {
        $request = $this->requestStack->getCurrentRequest();

        $oLogUserAuth = new LogUserAuth();
        $oLogUserAuth
            ->setIp($request->getClientIp())
            ->setBrowser(array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown')
            ->setUsername($request->get('_username'))
            ->setIsSuccess(false);

        $this->entityManager->persist($oLogUserAuth);
        $this->entityManager->flush();
    }
}
