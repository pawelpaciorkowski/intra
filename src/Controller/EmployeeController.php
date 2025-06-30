<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Employee;
use App\Entity\EmployeeImport;
use App\Form\Type\EmployeeImportType;
use App\Form\Type\EmployeeType;
use App\Services\Component\ParameterBag;
use App\Services\DepartmentService;
use App\Services\Employee\Exception\ImportException;
use App\Services\Employee\ImportService;
use App\Services\EmployeeService;
use App\Services\RequestService;
use App\Services\SettingService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function array_merge;
use function ceil;
use function count;

#[Route("/employee")]
final class EmployeeController extends AbstractController
{
    #[Route("/list/{page}", name: "employee", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        EntityManagerInterface $entityManager,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $entityManager->getRepository(Employee::class)->findAllByParams(
            new ParameterBag(
                array_merge(
                    $requestService->sortHandle()->getQuery(),
                    [
                        'page' => $page,
                        'rows_per_page' => $settingService->getSetting('rows_per_page', $this->getUser()),
                    ]
                )
            )
        );
        $rowsCount = count($rows);

        if ($rowsCount) {
            if (!$page) {
                return $this->redirectToRoute('employee', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('employee', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'employee/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    #[Route("/search", name: "employee-search")]
    public function search(DepartmentService $departmentService, EmployeeService $employeeService): Response
    {
        return $this->render(
            'employee/search.html.twig',
            [
                'departments' => $departmentService->findAllByParams(),
                'employees' => $employeeService->findAllByParams(new ParameterBag(['orderBy' => 'e.surname, e.name'])),
            ]
        );
    }

    #[Breadcrumb('Lista pracowników')]
    #[Route("/search/public", name: "employee-search-public")]
    public function searchPublic(DepartmentService $departmentService, EmployeeService $employeeService): Response
    {
        return $this->render(
            'employee/search-public.html.twig',
            [
                'departments' => $departmentService->findAllByParams(),
                'employees' => $employeeService->findAllByParams(new ParameterBag(['orderBy' => 'e.surname, e.name'])),
            ]
        );
    }

    #[Breadcrumb("Nowy pracownik")]
    #[Route("/create", name: "employee-create")]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $employee = new Employee();

        $form = $this->createForm(EmployeeType::class, $employee);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($employee);
            $entityManager->flush();

            $this->addFlash('notice', 'Poprawnie dodano nowego pracownika.');

            return $this->redirectToRoute('employee');
        }

        return $this->render(
            'employee/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Breadcrumb('Edycja pracownika')]
    #[Route("/{id}/update", name: "employee-update", requirements: ["id" => "\d+"])]
    public function update(Employee $employee, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('employee');
        }

        return $this->render(
            'employee/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $employee->getName(),
            ]
        );
    }

    #[Route("/{id}/delete", name: "employee-delete", requirements: ["id" => "\d+"])]
    public function delete(Employee $employee, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($employee);

        try {
            $entityManager->flush();
            $this->addFlash('notice', 'Pracownik został skasowany.');
        } /* @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash(
                'error',
                'Nie można usunąć tego pracownika, ponieważ jest on powiązany z innymi elementami.'
            );
        }

        return $this->redirectToRoute('employee');
    }

    #[Route("/import", name: "employee-import")]
    public function import(Request $request, ImportService $importService): Response
    {
        $form = new EmployeeImport();

        $form = $this->createForm(EmployeeImportType::class, $form);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $importService->import($form->getData()->getFile(), $form->getData()->getMode());
                $this->addFlash('notice', 'Poprawnie przetworzono przesłany plik.');
            } catch (ImportException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }

            return $this->redirectToRoute('employee-import');
        }

        return $this->render(
            'employee/import.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
