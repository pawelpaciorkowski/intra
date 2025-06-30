<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\LogDbTracking;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class LogDbTrackingRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogDbTracking::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('l')
            ->from(LogDbTracking::class, 'l');

        return $this
            ->includeFilterQueryData(
                [
                    'action' => ['l.action'],
                    'logged-at' => ['l.loggedAt'],
                    'object-id' => ['l.objectId'],
                    'object-class' => ['l.objectClass'],
                    'version' => ['l.version'],
                    'data' => ['l.data'],
                    'login' => ['l.username'],
                    'query' => [
                        'l.action',
                        'l.loggedAt',
                        'l.objectId',
                        'l.objectClass',
                        'l.version',
                        'l.data',
                        'l.username',
                    ],
                ]
            )
            ->where(['id' => 'l.id'])
            ->order('l.loggedAt', 'DESC')
            ->return();
    }
}
