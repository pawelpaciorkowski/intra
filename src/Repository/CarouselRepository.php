<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Carousel;
use App\Repository\Traits\QueryBuilderExtension;
use App\Services\Component\ParameterBagInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class CarouselRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Carousel::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['c', 'l', 'p'])
            ->from(Carousel::class, 'c')
            ->leftJoin('c.link', 'l')
            ->leftJoin('c.page', 'p');

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['c.title'],
                    'date' => ['c.date'],
                    'shortText' => ['c.shortText'],
                    'query' => ['c.title', 'c.date', 'c.shortText'],
                ]
            )
            ->where(['is-active' => 'c.isActive', 'id' => 'c.id'])
            ->order('c.sort')
            ->return();
    }
}
