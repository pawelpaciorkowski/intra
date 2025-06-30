<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Day;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class DayRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Day::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['d'])
            ->from(Day::class, 'd');

        if ($parameterBag && $parameterBag->has('start-at') && $parameterBag->get('start-at')) {
            $this->query
                ->andWhere('d.date >= :startAt')
                ->setParameter('startAt', $parameterBag->get('start-at'));
        }

        if ($parameterBag && $parameterBag->has('end-at') && $parameterBag->get('end-at')) {
            $this->query
                ->andWhere('d.date <= :endAt')
                ->setParameter('endAt', $parameterBag->get('end-at'));
        }

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['d.name'],
                    'date' => ['d.date'],
                    'query' => ['d.date', 'd.name'],
                ]
            )
            ->where(['id' => 'd.id', 'day' => 'd.date'])
            ->order('d.date')
            ->return(true);
    }
}
