<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Section;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class SectionRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Section::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['s'])
            ->from(Section::class, 's');

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['s.name'],
                    'query' => ['s.name'],
                ]
            )
            ->where(['id' => 's.id'])
            ->order('s.name')
            ->return();
    }
}
