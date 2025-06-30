<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Department;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

class DepartmentRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['d'])
            ->from(Department::class, 'd');

        if ($parameterBag) {
            if ($parameterBag->has('parent')) {
                if (!$parameterBag->get('parent')) {
                    $this->query->andWhere('d.parent is null');
                } else {
                    $this->query->andWhere('d.parent = :parent')->setParameter('parent', $parameterBag->get('parent'));
                }
            }
        }

        return $this
            ->where(['id' => 'd.id'])
            ->order('d.lft')
            ->return();
    }

    public function fetchAllCount()
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['count(d)'])
            ->from(Department::class, 'd')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function removeAll()
    {
        $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->delete()
            ->from(Department::class, 'department')
            ->getQuery()
            ->execute();
    }

    public function removeExcept($ids): void
    {
        $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->delete()
            ->from(Department::class, 'department')
            ->where('department.id NOT IN (:departmentIds)')
            ->setParameter('departmentIds', $ids)
            ->getQuery()
            ->execute();
    }
}
