<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SpecialTemplate;
use App\Repository\Traits\QueryBuilderExtension;
use App\Services\Component\ParameterBagInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class SpecialTemplateRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpecialTemplate::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['st', 's'])
            ->from(SpecialTemplate::class, 'st')
            ->leftJoin('st.specials', 's');

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['st.name'],
                    'template' => ['st.template'],
                    'query' => ['st.name', 'st.template', 's.title'],
                ]
            )
            ->where(['id' => 'st.id'])
            ->order('st.id')
            ->return();
    }
}
