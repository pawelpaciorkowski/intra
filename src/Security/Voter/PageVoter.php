<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Page;
use App\Services\UserService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class PageVoter extends Voter
{
    public const string CREATE = 'create';
    public const string READ = 'read';
    public const string UPDATE = 'update';
    public const string DELETE = 'delete';

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::READ, self::UPDATE, self::DELETE], true) && $subject instanceof Page;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        // Admin has always access
        if (array_intersect(
            [
                ...UserService::ROLES_ADMIN,
            ],
            $user->getRoles()
        )
        ) {
            return true;
        }

        foreach ($subject->getCategories() as $category) {
            foreach ($user->getCategories() as $userCategory) {
                if ($userCategory->getId() === $category->getId()) {
                    return true;
                }
            }
        }

        return false;
    }
}
