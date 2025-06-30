<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\ISOCategory;
use App\Form\Type\ISOCategoryType;
use App\Services\ISOCategoryService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/iso-category")] final class ISOCategoryController extends AbstractController
{
    #[Route("", name: "iso-category")]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $rows = $entityManager->getRepository(ISOCategory::class)->findAllByParams();

        return $this->render(
            'iso-category/list.html.twig',
            [
                'rows' => $rows,
            ]
        );
    }

    #[Route("/create", name: "iso-category-create")]
    #[Breadcrumb("Nowa kategoria")]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ISOCategoryService $ISOCategoryService,
    ): Response {
        $ISOCategory = new ISOCategory();

        $form = $this->createForm(ISOCategoryType::class, $ISOCategory);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ISOCategory);
            $entityManager->flush();

            $ISOCategoryService->updateISOCategory();

            $this->addFlash('notice', 'Poprawnie dodano kategorię.');

            return $this->redirectToRoute('iso-category');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route("/{id}/update", name: "iso-category-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Edycja kategorii")]
    public function update(
        ISOCategory $ISOCategory,
        Request $request,
        EntityManagerInterface $entityManager,
        ISOCategoryService $ISOCategoryService,
    ): Response {
        $form = $this->createForm(ISOCategoryType::class, $ISOCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $ISOCategoryService->updateISOCategory();

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('iso-category');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $ISOCategory->getName(),
            ]
        );
    }

    #[Route("/{id}/delete", name: "iso-category-delete", requirements: ["id" => "\d+"])]
    public function delete(
        ISOCategory $ISOCategory,
        EntityManagerInterface $entityManager,
        ISOCategoryService $ISOCategoryService,
    ): RedirectResponse {
        $entityManager->remove($ISOCategory);

        try {
            $entityManager->flush();
            $ISOCategoryService->updateISOCategory();
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash(
                'error',
                'Nie można usunąć tej kategorii, ponieważ jest ona powiązana z innymi elementami.'
            );
        }

        $this->addFlash('notice', 'Kategoria została usunięta.');

        return $this->redirectToRoute('iso-category');
    }
}
