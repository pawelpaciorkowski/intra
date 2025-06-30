<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Field;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class FieldRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Field::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['f', 's'])
            ->from(Field::class, 'f')
            ->leftJoin('f.settings', 's');

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['f.name'],
                    'type' => ['f.type'],
                    'query' => ['f.name', 'f.type', 'f.langType', 'f.className'],
                ]
            )
            ->where(['id' => 'f.id'])
            ->order('f.name')
            ->return();
    }
}
