<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Box;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Component\ParameterBagInterface;

final class BoxService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        return $this->entityManager->getRepository(Box::class)->findAllByParams($parameterBag);
    }

    public function resort(): void
    {
        $boxes = $this->entityManager->getRepository(Box::class)->findAll();

        $i = 1;
        foreach ($boxes as $box) {
            $box->setSort($i);
            $i++;
        }

        $this->entityManager->flush();
    }

    public function setSort(array $sort): bool
    {
        $i = 1;

        foreach ($sort as $id) {
            $this->entityManager
                ->createQueryBuilder()
                ->update(Box::class, 'b')
                ->set('b.sort', $i)
                ->where('b.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->execute();
            $i++;
        }

        return true;
    }
}
