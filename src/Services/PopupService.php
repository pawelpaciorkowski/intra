<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Popup;
use App\Services\Component\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;

final class PopupService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        return $this->entityManager->getRepository(Popup::class)->findAllByParams($parameterBag);
    }

    public function findRandom(): ?Popup
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('p')
            ->from(Popup::class, 'p')
            ->where('p.isActive = :is_active')
            ->setParameter('is_active', true)
            ->orderBy('rand()')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
