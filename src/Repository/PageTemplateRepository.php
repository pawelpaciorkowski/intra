<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PageTemplate;
use App\Repository\Traits\QueryBuilderExtension;
use App\Services\Component\ParameterBagInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class PageTemplateRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageTemplate::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['pt', 'p'])
            ->from(PageTemplate::class, 'pt')
            ->leftJoin('pt.pages', 'p');

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['pt.name'],
                    'template' => ['pt.template'],
                    'query' => ['pt.name', 'pt.template', 'p.title'],
                ]
            )
            ->where(['id' => 'pt.id'])
            ->order('pt.id')
            ->return();
    }
}
