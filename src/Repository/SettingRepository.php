<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Setting;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class SettingRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setting::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['s', 'f'])
            ->from(Setting::class, 's')
            ->join('s.field', 'f')
            ->join('s.section', 'e');

        if ($parameterBag && $parameterBag->has('user') && $parameterBag->get('user') instanceof UserInterface) {
            $this
                ->query
                ->leftJoin('s.link', 'l')
                ->leftJoin('l.roles', 'r')
                ->leftJoin('r.teams', 't')
                ->leftJoin('t.users', 'u')
                ->andWhere('s.link is NULL or u = :user')
                ->setParameter('user', $parameterBag->get('user'));

            $this
                ->query
                ->leftJoin('s.roles', 'r2')
                ->andWhere('r2 is NULL or r2.name in (:roles)')
                ->setParameter('roles', $parameterBag->get('user')->getRoles());
        }

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['s.name'],
                    'is-active' => ['s.isActive'],
                    'key' => ['s.key'],
                    'field' => ['f.name'],
                    'default' => ['s.default'],
                    'section' => ['e.name'],
                    'query' => ['s.name', 's.key', 'f.name', 's.default', 'e.name', 's.min_value', 's.max_value'],
                ]
            )
            ->where(['id' => 's.id'])
            ->order('s.name')
            ->return();
    }
}
