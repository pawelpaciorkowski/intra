<?php

declare(strict_types=1);

namespace App\Services\Employee;

use App\Entity\Department;
use App\Entity\Employee;
use App\Entity\EmployeeImport;
use App\Entity\Phone;
use App\Repository\DepartmentRepository;
use App\Repository\EmployeeRepository;
use App\Services\DepartmentService;
use App\Services\Employee\Exception\ImportException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportService
{
    public function __construct(
        private readonly ReaderService $readerService,
        private readonly EntityManagerInterface $entityManager,
        private readonly DepartmentRepository $departmentRepository,
        private readonly DepartmentService $departmentService,
        private readonly EmployeeRepository $employeeRepository,
    ) {
    }

    /**
     * @throws ImportException
     */
    public function import(UploadedFile $file, string $mode): void
    {
        $this->entityManager->beginTransaction();
        if ($mode === EmployeeImport::MODE_REPLACE) {
            $this->departmentRepository->removeAll();
            $this->employeeRepository->removeAll();
        }

        try {
            [$employees, $departments] = $this->readerService->read($file);

            $this->department($departments);
            $this->employee($employees);
        } catch (Exception $e) {
            throw new ImportException($e->getMessage());
        }

        if ($mode === EmployeeImport::MODE_UPDATE) {
            $this->departmentRepository->removeExcept(
                array_map(static function ($item) {
                    return $item->getId();
                }, $departments)
            );

            $this->employeeRepository->removeExcept(
                array_map(static function ($item) {
                    return $item->getId();
                }, $employees)
            );
        }

        $this->departmentService->updateDepartments();

        $this->entityManager->flush();
        $this->entityManager->commit();
    }

    /**
     * @throws ImportException
     */
    private function department(array &$departments, int $i = 0): void
    {
        if (!$departments[$i]->getId()) {
            if ($departments[$i]->getParent()) {
                $index = null;

                foreach ($departments as $key => $department) {
                    if ($department->getName() === $departments[$i]->getParent()) {
                        $index = $key;
                    }
                }

                if (!$index) {
                    throw new ImportException(
                        sprintf(
                            "Dział nadrzędny \"%s\" nie został znaleziony w bazie danych w pliku",
                            $departments[$i]->getParent()
                        )
                    );
                }

                if (!$departments[$index]->getId()) {
                    $this->department($departments, $index);
                }

                $parentDepartment = $this->departmentRepository->find($departments[$index]->getId());

                if (!$parentDepartment) {
                    throw new ImportException(
                        sprintf(
                            "Dział nadrzędny \"%s\" nie został znaleziony w bazie danych",
                            $departments[$index]->getParent()
                        )
                    );
                }

                $department = $this->departmentRepository->findOneBy([
                    'name' => $departments[$i]->getName(),
                    'depth' => $parentDepartment->getDepth() + 1,
                ]);

                if (!$department) {
                    $department = Department::create(
                        $departments[$i]->getName(),
                        $parentDepartment->getDepth() + 1,
                        $parentDepartment->getLft(),
                        $parentDepartment->getRgt()
                    );

                    $this->entityManager->persist($department);
                } else {
                    $department->setParent($parentDepartment)
                        ->setDepth($parentDepartment->getDepth() + 1)
                        ->setLft(0)
                        ->setRgt(0);
                }

                $parentDepartment->addChild($department);
                $this->entityManager->flush();
            } else {
                $department = $this->departmentRepository->findOneBy([
                    'name' => $departments[$i]->getName(),
                    'depth' => 0,
                ]);

                if (!$department) {
                    $department = Department::create($departments[$i]->getName());
                    $this->entityManager->persist($department);
                    $this->entityManager->flush();
                }
            }
            $departments[$i]->setId($department->getId());
        }

        $c = count($departments);
        if ($i < $c - 1) {
            $this->department($departments, $i + 1);
        }
    }

    /**
     * @throws ImportException
     */
    private function employee(array $employees): void
    {
        foreach ($employees as $employee) {
            $entity = $this->employeeRepository->findOneBy(['externalId' => $employee->getExternalId()]);

            if (!$entity) {
                $entity = new Employee();
                $this->entityManager->persist($entity);
            }

            $entity->updateFromEmployee($employee);
            $this->updateEmployeePhones($entity, $employee);

            $department = $this->departmentRepository->findOneBy(['name' => $employee->getDepartment()]);
            if (!$department) {
                throw new ImportException(
                    sprintf("Dział \"%s\" wskazany dla pracownika nie został odnaleziony\"", $employee->getDepartment())
                );
            }
            $entity->addDepartment($department);

            $this->entityManager->flush();

            $employee->setId($entity->getId());
        }
    }

    private function updateEmployeePhones(Employee $entity, \App\Services\Employee\Entity\Employee $employee): void
    {
        $entity->removeAllPhones();

        if ($employee->getPhone()) {
            $phone = Phone::create($entity, true, $employee->getPhone());
            $this->entityManager->persist($phone);
        }
    }

}
