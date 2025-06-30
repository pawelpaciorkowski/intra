<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Role;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class RoleRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['r'])
            ->from(Role::class, 'r');

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['r.name'],
                    'query' => ['r.name'],
                ]
            )
            ->where(['id' => 'r.id'])
            ->order('r.name')
            ->return();
    }
}
