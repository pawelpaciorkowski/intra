<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\PasswordHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class PasswordHistoryService
{
    public function __construct(
        private PasswordHasherFactoryInterface $passwordHasherFactory,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function save(UserInterface $user): bool
    {
        if (empty($user->getPassword())) {
            return false;
        }

        $passwordHistory = new PasswordHistory();
        $passwordHistory
            ->setUser($user)
            ->setPassword($user->getPassword());

        $this->entityManager->persist($passwordHistory);
        $this->entityManager->flush();

        return true;
    }

    public function exists(UserInterface $user, string $password): bool
    {
        $passwordHistoryList = $this->entityManager->getRepository(PasswordHistory::class)->findByUser($user);

        foreach ($passwordHistoryList as $passwordHistory) {
            if ($this->passwordHasherFactory->getPasswordHasher($user)->verify(
                $passwordHistory->getPassword(),
                $password,
            )
            ) {
                return true;
            }
        }

        return false;
    }
}
