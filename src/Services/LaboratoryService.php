<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Laboratory;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Component\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function array_intersect;

final class LaboratoryService
{
    private $entityManager;
    private $tokenStorage;
    private $authorizationChecker;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function findAllByParams(?ParameterBag $parameterBag = null)
    {
        if (!$parameterBag) {
            $parameterBag = new ParameterBag();
        }

        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->tokenStorage->getToken()->getUser();
            if (array_intersect(UserService::ROLES_LABORATORY, $user->getRoles())) {
                $parameterBag->set('laboratory-user-id', $user->getId());
            } elseif (array_intersect(UserService::ROLES_REGION, $user->getRoles())) {
                $parameterBag->set('region-user-id', $user->getId());
            }
        }

        return $this->entityManager->getRepository(Laboratory::class)->findAllByParams($parameterBag);
    }
}
