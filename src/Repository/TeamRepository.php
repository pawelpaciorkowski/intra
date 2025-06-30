<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Team;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

class TeamRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['t', 'u', 'r'])
            ->from(Team::class, 't')
            ->leftJoin('t.users', 'u')
            ->leftJoin('t.role', 'r');

        if ($parameterBag && $parameterBag->has('roles')) {
            $this->query->andWhere('r.name IN (:roles)')->setParameter('roles', $parameterBag->get('roles'));
        }

        if ($parameterBag && $parameterBag->has('restrictForApi')) {
            $this
                ->query
                ->andWhere('t.uuid IN (:restrictForApi)')
                ->setParameter('restrictForApi', Team::ALLOWED_ENTITIES);
        }

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['t.name'],
                    'query' => ['t.name'],
                ]
            )
            ->where(['is-active' => 't.isActive', 'id' => 't.id', 'uuid' => 't.uuid'])
            ->order('t.name')
            ->return();
    }

    public function findByRoles(string $role): array
    {
        $query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['t'])
            ->from(Team::class, 't')
            ->join('t.role', 'r')
            ->where('r.name = :role')
            ->setParameter('role', $role);

        return $query->getQuery()->getResult();
    }

    public function isAccessableForApi(Team $team): bool
    {
        return in_array($team->getUuid(), Team::ALLOWED_ENTITIES, true);
    }
}
