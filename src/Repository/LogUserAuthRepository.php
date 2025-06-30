<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\LogUserAuth;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class LogUserAuthRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogUserAuth::class);
    }

    public function findLastByUser(UserInterface $user)
    {
        $query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('l')
            ->from(LogUserAuth::class, 'l')
            ->join('l.user', 'u')
            ->where('l.isSuccess = true')
            ->andWhere('l.user = :user')
            ->orderBy('l.attemptAt', 'DESC')
            ->setParameter('user', $user)
            ->setFirstResult(1)
            ->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['l', 'u'])
            ->from(LogUserAuth::class, 'l')
            ->leftJoin('l.user', 'u');

        if ($parameterBag && $parameterBag->has('last-24-hours') && $parameterBag->get('last-24-hours')) {
            $this
                ->query
                ->where('l.attemptAt > :hours24')
                ->setParameter('hours24', new DateTime('-24 hours'));
        }

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['u.name'],
                    'attempt-at' => ['l.attemptAt'],
                    'login' => ['l.username'],
                    'ip' => ['l.ip'],
                    'browser' => ['l.browser'],
                    'query' => ['u.name', 'l.attemptAt', 'l.username', 'l.ip', 'l.browser'],
                ]
            )
            ->where(
                [
                    'id' => 'l.id',
                    'user-id' => 'u.id',
                    'is-success' => 'l.isSuccess',
                ]
            )
            ->order('l.attemptAt', 'DESC')
            ->group()
            ->return();
    }
}
