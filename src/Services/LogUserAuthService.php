<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\LogUserAuth;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Component\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use function array_intersect;

final class LogUserAuthService
{
    private $entityManager;
    private $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        // Use restriction
        if ($parameterBag && $parameterBag->has('restrict') && $parameterBag->get('restrict')) {
            if (!array_intersect(UserService::ROLES_ADMIN, $user->getRoles())) {
                $parameterBag->add(['user-id' => $user->getId()]);
            }
        }

        return $this->entityManager->getRepository(LogUserAuth::class)->findAllByParams($parameterBag);
    }
}
