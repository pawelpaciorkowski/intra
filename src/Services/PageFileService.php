<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\PageCategory;
use App\Entity\PageFile;
use App\Services\Component\ParameterBag;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

use function is_array;

final class PageFileService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityService $entityService
    ) {
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws ORMException
     */
    public function findAllByParams($parameterBag = null)
    {
        if (is_array($parameterBag)) {
            $parameterBag = new ParameterBag($parameterBag);
        } elseif (null === $parameterBag) {
            $parameterBag = new ParameterBag();
        }

        return $this->entityService->findAllByParams(PageCategory::class, $parameterBag);
    }

    public function resort(PageCategory $pageCategory): void
    {
        $pageFiles = $this->entityManager->getRepository(PageFile::class)->findBy(['pageCategory' => $pageCategory]);

        $i = 1;
        foreach ($pageFiles as $box) {
            $box->setSort($i);
            $i++;
        }

        $this->entityManager->flush();
    }

    public function setSort(array $sort): bool
    {
        $i = 1;

        foreach ($sort as $id) {
            $this->entityManager
                ->createQueryBuilder()
                ->update(PageFile::class, 'if')
                ->set('if.sort', $i)
                ->where('if.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->execute();
            $i++;
        }

        return true;
    }
}
