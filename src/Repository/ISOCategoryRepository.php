<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ISOCategory;
use App\Repository\Traits\QueryBuilderExtension;
use App\Services\Component\ParameterBagInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ISOCategoryRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ISOCategory::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['ic', 'if'])
            ->from(ISOCategory::class, 'ic')
            ->leftJoin('ic.ISOFiles', 'if');

        if ($parameterBag) {
            if ($parameterBag->has('parent')) {
                if (!$parameterBag->get('parent')) {
                    $this->query->andWhere('ic.parent is null');
                } else {
                    $this->query->andWhere('ic.parent = :parent')->setParameter('parent', $parameterBag->get('parent'));
                }
            }
        }

        return $this
            ->includeFilterQueryData(
                [
                    'query' => ['ic.name', 'ic.description'],
                ]
            )
            ->where([
                'is-active' => 'ic.isActive',
                'id' => 'ic.id',
            ])
            ->order('ic.lft')
            ->return();
    }

    public function fetchAllCount()
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['count(m)'])
            ->from(ISOCategory::class, 'm')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function fetchPath(ISOCategory $menu): array
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['m'])
            ->from(ISOCategory::class, 'm')
            ->where('m.lft <= :lft')
            ->setParameter('lft', $menu->getLft())
            ->andWhere('m.rgt >= :rgt')
            ->setParameter('rgt', $menu->getRgt())
            ->orderBy('m.lft', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
