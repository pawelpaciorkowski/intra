<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Carousel;
use App\Services\Component\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;

final class CarouselService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        return $this->entityManager->getRepository(Carousel::class)->findAllByParams($parameterBag);
    }

    public function resort(): void
    {
        $boxes = $this->entityManager->getRepository(Carousel::class)->findAll();

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
                ->update(Carousel::class, 'c')
                ->set('c.sort', $i)
                ->where('c.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->execute();
            $i++;
        }

        return true;
    }
}
