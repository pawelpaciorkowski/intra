<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\ISOCategory;
use App\Entity\ISOFile;
use App\Services\Component\ParameterBag;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

use function is_array;

final class ISOFileService
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

        return $this->entityService->findAllByParams(ISOCategory::class, $parameterBag);
    }

    public function resort(ISOCategory $ISOCategory): void
    {
        $ISOFiles = $this->entityManager->getRepository(ISOFile::class)->findBy(['ISOCategory' => $ISOCategory]);

        $i = 1;
        foreach ($ISOFiles as $box) {
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
                ->update(ISOFile::class, 'if')
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
