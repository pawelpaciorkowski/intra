<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class CategoryRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['m', 'i', 'i2', 'l', 'p', 'ps'])
            ->from(Category::class, 'm')
            ->leftJoin('m.icon', 'i')
            ->leftJoin('m.additionalIcon', 'i2')
            ->leftJoin('m.link', 'l')
            ->leftJoin('m.page', 'p')
            ->leftJoin('m.pages', 'ps');

        if ($parameterBag) {
            if ($parameterBag->has('parent')) {
                if (!$parameterBag->get('parent')) {
                    $this->query->andWhere('m.parent is null');
                } else {
                    $this->query->andWhere('m.parent = :parent')->setParameter('parent', $parameterBag->get('parent'));
                }
            }
        }

        return $this
            ->includeFilterQueryData(
                [
                    'query' => ['m.name', 'm.description'],
                ]
            )
            ->where([
                'is-active' => 'm.isActive',
                'id' => 'm.id',
                'is-show-on-main-menu' => 'm.isShowOnMainMenu',
            ])
            ->order('m.lft')
            ->return();
    }

    public function fetchAllCount()
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['count(m)'])
            ->from(Category::class, 'm')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function fetchChildren(int $depth, int $lft, int $rgt): array
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['m'])
            ->from(Category::class, 'm')
            ->where('m.depth <= :depth')
            ->andWhere('m.lft > :lft')
            ->andWhere('m.rgt < :rgt')
            ->setParameter('depth', $depth + 1)
            ->setParameter('lft', $lft)
            ->setParameter('rgt', $rgt)
            ->getQuery()
            ->getResult();
    }

    public function fetchPath(Category $menu): array
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['m'])
            ->from(Category::class, 'm')
            ->where('m.lft <= :lft')
            ->setParameter('lft', $menu->getLft())
            ->andWhere('m.rgt >= :rgt')
            ->setParameter('rgt', $menu->getRgt())
            ->orderBy('m.lft', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchCategoryByRoute(string $route): ?Category
    {
        $categories = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['c', 'l'])
            ->from(Category::class, 'c')
            ->leftjoin('c.highlights', 'h')
            ->leftjoin('c.link', 'l')
            ->andWhere('h.name = :route or l.name = :route')
            ->setParameter('route', $route)
            ->getQuery()
            ->getResult();

        if ($categories) {
            return $categories[0];
        }

        return null;
    }
}
