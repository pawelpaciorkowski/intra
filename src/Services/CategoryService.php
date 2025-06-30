<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Category;
use App\Entity\Page;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Config\Definition\Exception\Exception;
use App\Services\Component\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

use function count;
use function in_array;
use function is_array;

final class CategoryService
{
    private $selectedCategory;

    private $selectedPath;

    private $loopDetector;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
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
                    $category = $this->entityManager->getRepository(Category::class)->find($row->id);

                    $category->setLft($row->left - 2);
                    $category->setRgt($row->right - 2);
                    $category->setDepth($row->depth);

                    if ($row->parent_id) {
                        $category->setParent($this->entityManager->getReference(Category::class, $row->parent_id));
                    } else {
                        $category->setParent();
                    }
                    $this->entityManager->persist($category);
                }
            }
            $this->entityManager->flush();
            $this->loopDetection();
            $this->entityManager->getConnection()->commit();

            $this->updateCategory();
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
            ->getRepository(Category::class)
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
            ->getRepository(Category::class)
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

        return $this->entityService->findAllByParams(Category::class, $parameterBag);
    }

    public function updateCategory(): self
    {
        $this->updateBranch();
        $this->entityManager->flush();

        return $this;
    }

    private function updateBranch(?Category $parent = null, $depth = 0, $lft = 1): int
    {
        $categories = $this
            ->entityManager
            ->getRepository(Category::class)
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

    public function getSelectedPath(?UserInterface $user)
    {
        if (!$this->selectedPath) {
            $this->readSelectedPath($user);
        }

        return $this->selectedPath;
    }

    private function readSelectedPath(?UserInterface $user): void
    {
        if (!$this->selectedCategory) {
            $this->readSelectedCategory($user);
        }

        if ($this->selectedCategory) {
            $this->selectedPath = $this->entityManager->getRepository(Category::class)->fetchPath(
                $this->selectedCategory
            );
        }
    }

    private function readSelectedCategory(?UserInterface $user): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return;
        }

        if ($request->attributes->get('_route') === 'category-view') {
            $this->selectedCategory = $this->entityManager->getRepository(Category::class)->find(
                $request->attributes->get('id')
            );
        } elseif ($request->attributes->get('_route') === 'page-view') {
            $page = $this->entityManager->getRepository(Page::class)->find($request->attributes->get('id'));
            if ($page->getCategories()) {
                $this->selectedCategory = $page->getCategories()[0];
            }
        } else {
            $this->selectedCategory = $this
                ->entityManager
                ->getRepository(Category::class)
                ->fetchCategoryByRoute($request->attributes->get('_route'));
        }
    }

    public function getSelectedLeaf(?User $user)
    {
        if (!$this->selectedCategory) {
            $this->readSelectedCategory($user);
        }

        return $this->selectedCategory;
    }
}
