<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Link;
use App\Entity\User;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use function array_intersect;
use function array_key_exists;
use function array_unique;
use function is_a;

final class SecurityService
{
    private $accessArray;
    private $tokenStorage;
    private $roleHierarchy;

    public function __construct(
        TagAwareAdapter $tagAwareAdapter,
        LinkService $linkService,
        TokenStorageInterface $tokenStorage,
        RoleHierarchyInterface $roleHierarchy
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->roleHierarchy = $roleHierarchy;

        $accessArray = $tagAwareAdapter->getItem('accessArray');

        if (!$accessArray->isHit()) {
            $accessArray->set($linkService->getAccessArray());
            $tagAwareAdapter->save($accessArray);
        }

        $this->accessArray = $accessArray->get();
    }

    public function hasAccess($routes, ?User $user = null): bool
    {
        $calculatedUser = $user ?? ($this->tokenStorage->getToken() && is_a(
            $this->tokenStorage->getToken()->getUser(),
            UserInterface::class
        ) ? $this->tokenStorage->getToken()->getUser() : null);

        $roles = $this->getUserInheritedRoles($calculatedUser);
        // If not array, make it array
        $routes = (array)$routes;

        foreach ($routes as $route) {
            if (is_a($route, Link::class)) {
                $route = $route->getName();
            }

            if (array_key_exists($route, $this->accessArray) && array_intersect($this->accessArray[$route], $roles)) {
                return true;
            }
        }

        return false;
    }

    public function getUserInheritedRoles(?User $user): array
    {
        $roles = ['ROLE_ANONYMOUS'];

        if ($user) {
            foreach ($user->getRoles() as $role) {
                $roles[] = $role;
            }
        }

        $userInheritedRolesNames = $this->roleHierarchy->getReachableRoleNames($roles);

        $roles = [];
        foreach ($userInheritedRolesNames as $role) {
            $roles[] = $role;
        }

        return array_unique($roles);
    }
}
