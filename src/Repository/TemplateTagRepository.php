<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TemplateTag;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class TemplateTagRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TemplateTag::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['t', 'te'])
            ->from(TemplateTag::class, 't')
            ->leftJoin('t.templates', 'te');

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['t.name'],
                    'tag' => ['t.tag'],
                    'query' => ['t.name', 't.tag'],
                ]
            )
            ->where(['is-active' => ['t.isActive', 'te.isActive'], 'id' => 't.id', 'tag' => 't.tag'])
            ->order('te.subject')
            ->return();
    }
}
