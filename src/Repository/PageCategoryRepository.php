<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PageCategory;
use App\Repository\Traits\QueryBuilderExtension;
use App\Services\Component\ParameterBagInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class PageCategoryRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageCategory::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['pc', 'pf', 'p'])
            ->from(PageCategory::class, 'pc')
            ->leftJoin('pc.pageFiles', 'pf')
            ->leftJoin('pc.page', 'p')
        ;

        if ($parameterBag) {
            if ($parameterBag->has('parent')) {
                if (!$parameterBag->get('parent')) {
                    $this->query->andWhere('pc.parent is null');
                } else {
                    $this->query->andWhere('pc.parent = :parent')->setParameter('parent', $parameterBag->get('parent'));
                }
            }
        }

        return $this
            ->includeFilterQueryData(
                [
                    'query' => ['pc.name', 'pc.description'],
                ]
            )
            ->where([
                'page-id' => 'p.id',
                'is-active' => 'pc.isActive',
                'id' => 'pc.id',
            ])
            ->order('pc.lft')
            ->return();
    }

    public function fetchAllCount()
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['count(pc)'])
            ->from(PageCategory::class, 'pc')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function fetchPath(PageCategory $menu): array
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['pc'])
            ->from(PageCategory::class, 'pc')
            ->where('pc.lft <= :lft')
            ->setParameter('lft', $menu->getLft())
            ->andWhere('pc.rgt >= :rgt')
            ->setParameter('rgt', $menu->getRgt())
            ->orderBy('pc.lft', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
