<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CategoryTemplate;
use App\Repository\Traits\QueryBuilderExtension;
use App\Services\Component\ParameterBagInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class CategoryTemplateRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryTemplate::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['ct', 'c'])
            ->from(CategoryTemplate::class, 'ct')
            ->leftJoin('ct.categories', 'c');

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['ct.name'],
                    'template' => ['ct.template'],
                    'query' => ['ct.name', 'ct.template', 'c.name'],
                ]
            )
            ->where(['id' => 'ct.id'])
            ->order('ct.id')
            ->return();
    }
}
