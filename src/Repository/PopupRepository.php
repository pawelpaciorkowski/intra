<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Popup;
use App\Repository\Traits\QueryBuilderExtension;
use App\Services\Component\ParameterBagInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class PopupRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Popup::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['p'])
            ->from(Popup::class, 'p');

        return $this
            ->includeFilterQueryData(
                [
                    'title' => ['p.title'],
                    'query' => ['p.title', 'p.content'],
                ]
            )
            ->where(['is-active' => 'p.isActive', 'id' => 'p.id'])
            ->order('p.title')
            ->return();
    }
}
