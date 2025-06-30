<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Department;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use App\Services\Component\ParameterBag;
use App\Services\Component\ParameterBagInterface;

use function count;
use function in_array;

final class DepartmentService
{
    private $loopDetector;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setOrder(array $data): bool
    {
        $this->entityManager->getConnection()->beginTransaction();

        try {
            foreach ($data as $row) {
                if ($row->id) {
                    $department = $this->entityManager->getRepository(Department::class)->findAllByParams(
                        new ParameterBag(['id' => $row->id])
                    );

                    $department->setLft($row->left - 2);
                    $department->setRgt($row->right - 2);
                    $department->setDepth($row->depth);

                    if ($row->parent_id) {
                        $department->setParent($this->entityManager->getReference(Department::class, $row->parent_id));
                    } else {
                        $department->setParent();
                    }
                    $this->entityManager->persist($department);
                }
            }
            $this->entityManager->flush();
            $this->loopDetection();
            $this->entityManager->getConnection()->commit();

            $this->updateDepartments();
        } catch (Exception) {
            $this->entityManager->getConnection()->rollBack();

            return false;
        }

        return true;
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        return $this
            ->entityManager
            ->getRepository(Department::class)
            ->findAllByParams($parameterBag);
    }

    private function loopDetection(): void
    {
        $this->loopDetector = [];
        $this->loopDetectionWorker();

        $count = $this
            ->entityManager
            ->getRepository(Department::class)
            ->fetchAllCount();

        // if worker founds less departments then exists in database, throw Exception
        if ((int)$count !== count($this->loopDetector)) {
            throw new Exception('loop detected!');
        }
    }

    private function loopDetectionWorker($parent = null): void
    {
        $departments = $this
            ->entityManager
            ->getRepository(Department::class)
            ->findAllByParams(new ParameterBag(['parent' => $parent]));

        foreach ($departments as $department) {
            if (in_array($department->getId(), $this->loopDetector, true)) {
                throw new Exception('loop detected!');
            }
            $this->loopDetector[] = $department->getId();

            $this->loopDetectionWorker($department);
        }
    }

    public function updateDepartments(): self
    {
        $this->updateBranch();
        $this->entityManager->flush();

        return $this;
    }

    private function updateBranch(?Department $parent = null, $depth = 0, $lft = 1): int
    {
        $departments = $this
            ->entityManager
            ->getRepository(Department::class)
            ->findAllByParams(new ParameterBag(['parent' => $parent]));

        $start = $rgt = $lft;

        foreach ($departments as $department) {
            $count = $this->updateBranch($department, $depth + 1, $lft + 1);

            $rgt = $lft + $count + 1;
            if ($count) {
                ++$rgt;
            }

            $department
                ->setDepth($depth)
                ->setLft($lft)
                ->setRgt($rgt);

            // TODO: potrzebne?
            $this->entityManager->persist($department);

            $lft = $rgt + 1;
        }

        return $rgt - $start;
    }
}
