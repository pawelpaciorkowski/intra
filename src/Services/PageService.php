<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Category;
use App\Entity\Page;
use App\Services\Component\ParameterBag;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Component\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PageService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, private TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        if (!$parameterBag) {
            $parameterBag = new ParameterBag();
        }

        if ($this->tokenStorage->getToken()) {
            $user = $this->tokenStorage->getToken()->getUser();
            if (array_intersect(UserService::ROLES_EDITOR, $user->getRoles())) {
                $parameterBag->set('editor-user-id', $user->getId());
            }
        }

        return $this->entityManager->getRepository(Page::class)->findAllByParams($parameterBag);
    }

    public function findPagesByCategory(Category $category): array
    {
        return $this->entityManager->getRepository(Page::class)->findPagesByCategory($category);
    }
}
