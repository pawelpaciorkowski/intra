<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Employee;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

class EmployeeRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['e', 'de', 'ph'])
            ->from(Employee::class, 'e')
            ->leftJoin('e.departments', 'de')
            ->leftJoin('e.phones', 'ph');

        if ($parameterBag && $parameterBag->has('department-id')) {
            $this
                ->query
                ->andWhere(
                    'de.lft >= (select d2.lft from App\Entity\Department d2 where d2.id = :department) 
                    and 
                    de.rgt <= (select d3.rgt from App\Entity\Department d3 where d3.id = :department)'
                )
                ->setParameter('department', $parameterBag->get('department-id'));
        }

        return $this
            ->includeFilterQueryData([
                'name' => 'e.name',
                'surname' => 'e.surname',
                'email' => 'e.email',
                'position' => 'e.position',
                'department' => 'de.name',
                'phone' => 'ph.number',
                'query' => [
                    'de.name',
                    'ph.number',
                    'e.name',
                    'e.surname',
                    'e.email',
                    'e.position',
                ],
            ])
            ->where([
                'id' => 'de.id',
            ])
            ->order('de.lft')
            ->return();
    }

    public function removeAll(): void
    {
        $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->delete()
            ->from(Employee::class, 'employee')
            ->getQuery()
            ->execute();
    }

    public function removeExcept(array $ids): void
    {
        $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->delete()
            ->from(Employee::class, 'employee')
            ->where('employee.id NOT IN (:employeeIds)')
            ->setParameter('employeeIds', $ids)
            ->getQuery()
            ->execute();
    }
}
