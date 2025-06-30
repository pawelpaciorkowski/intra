<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\LogRequest;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class LogRequestRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogRequest::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['l', 'u'])
            ->from(LogRequest::class, 'l')
            ->leftJoin('l.user', 'u');

        return $this
            ->includeFilterQueryData(
                [
                    'url' => ['l.url'],
                    'method' => ['l.method'],
                    'content' => ['l.content'],
                    'ip' => ['l.ip'],
                    'user' => ['u.name', 'u.surname'],
                    'created-at' => ['l.createdAt'],
                    'query' => [
                        'l.createdAt',
                        'l.url',
                        'l.method',
                        'l.content',
                        'l.ip',
                        'u.name',
                        'u.surname',
                    ],
                ]
            )
            ->where(['id' => 'l.id'])
            ->order('l.createdAt', 'DESC')
            ->return();
    }
}
