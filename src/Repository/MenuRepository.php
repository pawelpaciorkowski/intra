<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Menu;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class MenuRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['m', 'i', 'i2', 'l'])
            ->from(Menu::class, 'm')
            ->leftJoin('m.icon', 'i')
            ->leftJoin('m.additionalIcon', 'i2')
            ->leftJoin('m.link', 'l')
            ->leftJoin('l.roles', 'r')
            ->leftJoin('m.roles', 'r2');

        if ($parameterBag) {
            if ($parameterBag->has('roles')) {
                $this->query->andWhere('r2.name IN (:roles)')->setParameter('roles', $parameterBag->get('roles'));
            }

            if ($parameterBag->has('parent')) {
                if (!$parameterBag->get('parent')) {
                    $this->query->andWhere('m.parent is null');
                } else {
                    $this->query->andWhere('m.parent = :parent')->setParameter('parent', $parameterBag->get('parent'));
                }
            }
        }

        return $this
            ->where(['is-active' => 'm.isActive', 'id' => 'm.id'])
            ->order('m.lft')
            ->return();
    }

    public function fetchAllCount()
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['count(m)'])
            ->from(Menu::class, 'm')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function fetchChildren(int $depth, int $lft, int $rgt): array
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['m'])
            ->from(Menu::class, 'm')
            ->where('m.depth <= :depth')
            ->andWhere('m.lft > :lft')
            ->andWhere('m.rgt < :rgt')
            ->setParameter('depth', $depth + 1)
            ->setParameter('lft', $lft)
            ->setParameter('rgt', $rgt)
            ->getQuery()
            ->getResult();
    }

    public function fetchPath(Menu $menu): array
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['m'])
            ->from(Menu::class, 'm')
            ->where('m.lft <= :lft')
            ->setParameter('lft', $menu->getLft())
            ->andWhere('m.rgt >= :rgt')
            ->setParameter('rgt', $menu->getRgt())
            ->orderBy('m.lft', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchMenuByRoute(string $route, array $roles)
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['m', 'l'])
            ->from(Menu::class, 'm')
            ->join('m.highlights', 'h')
            ->join('m.link', 'l')
            ->leftJoin('m.roles', 'r')
            ->where('m.lft = (m.rgt - 1)')
            ->andWhere('h.name = :route')
            ->setParameter('route', $route)
            ->andWhere('r.name IN (:roles)')
            ->setParameter('roles', $roles)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
