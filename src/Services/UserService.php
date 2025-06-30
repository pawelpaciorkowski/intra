<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\User;
use App\Event\EmailEvent;
use App\Services\Exception\RecoverPasswordException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Component\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use function is_array;

final class UserService
{
    public const array ROLES_ADMIN = ['ROLE_ADMIN'];
    public const array ROLES_EDITOR = ['ROLE_EDITOR'];
    public const array ROLES_SUPERADMIN = ['ROLE_SUPER_ADMIN'];
    public const array ROLES_REGION = ['ROLE_REGION'];
    public const array ROLES_LABORATORY = ['ROLE_LABORATORY'];
    public const array ROLES_COLLECTION_POINT = ['ROLE_COLLECTION_POINT'];


    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
        private readonly TicketService $ticketService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PasswordHistoryService $passwordHistoryService
    ) {
    }

    public function findAllByParams($parameterBag = null)
    {
        if (is_array($parameterBag)) {
            $parameterBag = new ParameterBag($parameterBag);
        } elseif (null === $parameterBag) {
            $parameterBag = new ParameterBag();
        }

        return $this->entityManager->getRepository(User::class)->findAllByParams($parameterBag);
    }

    public function setUserPassword(User $user, $plaintextPassword): self
    {
        if (!empty($plaintextPassword)) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            $user
                ->setPassword($hashedPassword)
                ->setPasswordChangeAt(new DateTime());
        }

        $this->passwordHistoryService->save($user);

        return $this;
    }

    public function save($user, string $password = null): int
    {
        if ($password) {
            $this->setUserPassword($user, $password);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user->getId();
    }

    public function recoverPasswordByUsernameOrEmail(string $usernameOrEmail): void
    {
        $user = $this->entityManager->getRepository(User::class)->findUserByUsernameOrEmail($usernameOrEmail);

        if (!$user) {
            throw new RecoverPasswordException('Nie znaleziono użytkownika o podanym loginie lub adresie e-mail');
        }

        if (!$user->getIsActive()) {
            throw new RecoverPasswordException('Konto jest zablokowane');
        }

        if ($user = $this->entityManager->getRepository(User::class)->findUserByUsernameOrEmail($usernameOrEmail)) {
            $emailEvent = new EmailEvent(
                'new-password',
                [
                    'user' => $user,
                    'ticket' => $this->ticketService->generateTicketForUser($user),
                ]
            );
            $this->eventDispatcher->dispatch($emailEvent);
        }
    }
}
