<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use DateTime;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class PasswordSubscriber implements EventSubscriberInterface
{
    private const string PASSWORD_CHANGE_ROUTE = 'password-change';

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    #[ArrayShape([KernelEvents::REQUEST => "string[]"])]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }

    /**
     * @throws Exception
     */
    public function onKernelRequest(RequestEvent $event): bool
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return false;
        }

        // User must be logged in
        if (!$this->tokenStorage->getToken() || !$this->authorizationChecker->isGranted(
            'IS_AUTHENTICATED_REMEMBERED'
        )
        ) {
            return false;
        }

        // User is not switched to another user
        if ($this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            return false;
        }

        // User has permanent password
        if (!$this->tokenStorage->getToken()->getUser()->getIsPasswordChangeRequired()) {
            return false;
        }

        // Password change date must not be old
        $passwordChangeAt = new DateTime('-' . User::MAX_PASSWORD_AGE . ' seconds');
        $currentPasswordChangeAt = $this->tokenStorage->getToken()->getUser()->getPasswordChangeAt();
        if ($currentPasswordChangeAt instanceof DateTime && $currentPasswordChangeAt > $passwordChangeAt) {
            return false;
        }

        // Requested route should be other than password change route
        if (self::PASSWORD_CHANGE_ROUTE === $this->requestStack->getCurrentRequest()->get('_route')) {
            return false;
        }

        // save old url for future redirect
        $this->requestStack->getSession()->set(
            'password-change-url',
            $this->requestStack->getCurrentRequest()->getUri()
        );

        $url = $this->urlGenerator->generate(self::PASSWORD_CHANGE_ROUTE);
        $event->setResponse(new RedirectResponse($url));

        return true;
    }
}
