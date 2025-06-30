<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\PageCategory;
use App\Services\Component\ParameterBag;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Config\Definition\Exception\Exception;

use function count;
use function in_array;
use function is_array;

final class PageCategoryService
{
    private $selectedPageCategory;

    private $selectedPath;

    private $loopDetector;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityService $entityService
    ) {
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws ORMException
     */
    public function setOrder(array $data): bool
    {
        $this->entityManager->getConnection()->beginTransaction();

        try {
            foreach ($data as $row) {
                if ($row->id) {
                    $category = $this->entityManager->getRepository(PageCategory::class)->find($row->id);

                    $category->setLft($row->left - 2);
                    $category->setRgt($row->right - 2);
                    $category->setDepth($row->depth);

                    if ($row->parent_id) {
                        $category->setParent($this->entityManager->getReference(PageCategory::class, $row->parent_id));
                    } else {
                        $category->setParent();
                    }
                }
            }
            $this->entityManager->flush();
            $this->loopDetection();
            $this->entityManager->getConnection()->commit();

            $this->updatePageCategory();
        } catch (Exception) {
            $this->entityManager->getConnection()->rollBack();

            return false;
        }

        return true;
    }

    private function loopDetection(): void
    {
        $this->loopDetector = [];
        $this->loopDetectionWorker();

        $count = $this
            ->entityManager
            ->getRepository(PageCategory::class)
            ->fetchAllCount();

        // if worker founds less categories then exists in database, throw Exception
        if ((int)$count !== count($this->loopDetector)) {
            throw new Exception('loop detected!');
        }
    }

    private function loopDetectionWorker($parent = null): void
    {
        $categories = $this
            ->entityManager
            ->getRepository(PageCategory::class)
            ->findAllByParams(new ParameterBag(['parent' => $parent]));

        foreach ($categories as $category) {
            if (in_array($category->getId(), $this->loopDetector, true)) {
                throw new Exception('loop detected!');
            }
            $this->loopDetector[] = $category->getId();

            $this->loopDetectionWorker($category);
        }
    }

    public function findAllByParams($parameterBag = null)
    {
        if (is_array($parameterBag)) {
            $parameterBag = new ParameterBag($parameterBag);
        } elseif (null === $parameterBag) {
            $parameterBag = new ParameterBag();
        }

        return $this->entityService->findAllByParams(PageCategory::class, $parameterBag);
    }

    public function updatePageCategory(): self
    {
        $this->updateBranch();
        $this->entityManager->flush();

        return $this;
    }

    private function updateBranch(?PageCategory $parent = null, $depth = 0, $lft = 1): int
    {
        $categories = $this
            ->entityManager
            ->getRepository(PageCategory::class)
            ->findAllByParams(new ParameterBag(['parent' => $parent]));

        $start = $rgt = $lft;

        foreach ($categories as $category) {
            $count = $this->updateBranch($category, $depth + 1, $lft + 1);

            $rgt = $lft + $count + 1;
            if ($count) {
                ++$rgt;
            }

            $category
                ->setDepth($depth)
                ->setLft($lft)
                ->setRgt($rgt);

            $this->entityManager->persist($category);

            $lft = $rgt + 1;
        }

        return $rgt - $start;
    }
}
