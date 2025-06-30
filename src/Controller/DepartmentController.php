<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Department;
use App\Form\Type\DepartmentType;
use App\Services\DepartmentService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\Component\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/department")] final class DepartmentController extends AbstractController
{
    #[Route("", name: "department")]
    public function list(DepartmentService $departmentService): Response
    {
        $rows = $departmentService->findAllByParams();

        return $this->render('department/list.html.twig', ['rows' => $rows]);
    }

    #[Route("/create", name: "department-create")]
    #[Breadcrumb("Nowy departament")]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        DepartmentService $departmentService
    ): Response {
        $department = new Department();

        $form = $this->createForm(DepartmentType::class, $department);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($department);
            $entityManager->flush();

            $departmentService->updateDepartments();

            $this->addFlash('notice', 'Poprawnie dodano pozycję department.');

            return $this->redirectToRoute('department');
        }

        return $this->render('partials/form.html.twig', ['form' => $form->createView()]);
    }

    #[Route("/{id}/update", name: "department-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Edycja departamentu")]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        DepartmentService $departmentService
    ): Response {
        $department = $departmentService->findAllByParams(new ParameterBag(['id' => $id]));

        if (!$department) {
            throw $this->createNotFoundException('Taki departament nie istnieje.');
        }

        $form = $this->createForm(DepartmentType::class, $department);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $departmentService->updateDepartments();

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('department');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $department->getName(),
            ]
        );
    }

    #[Route("/{id}/delete", name: "department-delete", requirements: ["id" => "\d+"])]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager,
        DepartmentService $departmentService
    ): RedirectResponse {
        $department = $departmentService->findAllByParams(new ParameterBag(['id' => $id]));
        if (!$department) {
            throw $this->createNotFoundException('Taki departament nie istnieje.');
        }

        $entityManager->remove($department);

        try {
            $entityManager->flush();
            $departmentService->updateDepartments();
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Nie można usunąć tej pozycji, ponieważ jest ona powiązana z innymi elementami.');
        }

        $this->addFlash('notice', 'Element department został usunięty.');

        return $this->redirectToRoute('department');
    }
}
