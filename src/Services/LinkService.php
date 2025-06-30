<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Link;
use App\Services\Component\ParameterBag;
use App\Services\Exception\LinkNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

final class LinkService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getAccessArray(): array
    {
        $links = $this->entityManager->getRepository(Link::class)->findLinkWithRole();

        $result = [];

        foreach ($links as $link) {
            $roles = [];

            foreach ($link->getRoles() as $role) {
                $roles[] = $role->getName();
            }

            $result[$link->getName()] = $roles;
        }

        return $result;
    }

    public function findOneByRoute(string $route): Link
    {
        $links = $this->entityManager->getRepository(Link::class)->findAllByParams(
            new ParameterBag(['routeName' => $route])
        );
        if ($links) {
            return $links[0];
        }

        throw new LinkNotFoundException();
    }
}
