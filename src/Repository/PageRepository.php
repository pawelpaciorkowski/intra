<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Page;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class PageRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['p', 'c'])
            ->from(Page::class, 'p')
            ->leftJoin('p.categories', 'c');

        if ($parameterBag && $parameterBag->has('editor-user-id')) {
            $this->query
                ->leftJoin('c.users', 'u')
                ->where('u.id = :userId')
                ->setParameter('userId', $parameterBag->get('editor-user-id'));
        }

        return $this
            ->includeFilterQueryData(
                [
                    'id' => ['p.id'],
                    'page-id' => ['p.id'],
                    'title' => ['p.title'],
                    'short' => ['p.short'],
                    'published-at' => ['p.publishedAt'],
                    'long' => ['p.long'],
                    'category' => ['c.name'],
                    'query' => ['p.title', 'p.shortText', 'p.longText', 'c.name', 'p.publishedAt'],
                ]
            )
            ->where(['is-active' => 'p.isActive', 'id' => 'p.id'])
            ->order('p.title')
            ->return();
    }

    public function findPagesByCategory(Category $category, bool $onlyActive = true)
    {
        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['p'])
            ->from(Page::class, 'p')
            ->join('p.categories', 'c');

        if ($onlyActive) {
            $this->query->andWhere('p.isActive = true');
        }

        if ($category->getIsShowPagesInSubcategories()) {
            $this
                ->query
                ->andWhere('c.lft >= :lft and c.rgt <= :rgt')
                ->setParameter('lft', $category->getLft())
                ->setParameter('rgt', $category->getRgt());
        } else {
            $this
                ->query
                ->andWhere('c = :category')
                ->setParameter('category', $category);
        }

        return $this
            ->order('p.publishedAt', 'desc')
            ->return();
    }
}
