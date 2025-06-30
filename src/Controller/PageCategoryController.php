<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Page;
use App\Entity\PageCategory;
use App\Form\Type\PageCategoryType;
use App\Services\PageCategoryService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/page")] final class PageCategoryController extends AbstractController
{
    #[Route("/{id}/page-category/create", name: "page-category-create", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Nowa kategoria")]
    public function create(
        Page $page,
        Request $request,
        EntityManagerInterface $entityManager,
        PageCategoryService $pageCategoryService,
    ): Response {
        $pageCategory = new PageCategory();
        $pageCategory->setPage($page);

        $form = $this->createForm(PageCategoryType::class, $pageCategory);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($pageCategory);
            $entityManager->flush();

            $pageCategoryService->updatePageCategory();

            $this->addFlash('notice', 'Poprawnie dodano kategorię.');

            return $this->redirectToRoute('page-file', ['id' => $page->getId()]);
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route("/{id}/page-category/{pageCategory}/update", name: "page-category-update", requirements: ["id" => "\d+", "pageCategory" => "\d+"])]
    #[Breadcrumb("Edycja kategorii")]
    public function update(
        Page $page,
        PageCategory $pageCategory,
        Request $request,
        EntityManagerInterface $entityManager,
        PageCategoryService $pageCategoryService,
    ): Response {
        $form = $this->createForm(PageCategoryType::class, $pageCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $pageCategoryService->updatePageCategory();

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('page-file', ['id' => $page->getId()]);
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'page' => $page,
                'form' => $form->createView(),
                'subtitle' => $pageCategory->getName(),
            ]
        );
    }

    #[Route("/{id}/page-category/{pageCategory}/delete", name: "page-category-delete", requirements: ["id" => "\d+", "pageCategory" => "\d+"])]
    public function delete(
        Page $page,
        PageCategory $pageCategory,
        EntityManagerInterface $entityManager,
        PageCategoryService $pageCategoryService,
    ): RedirectResponse {
        $entityManager->remove($pageCategory);

        try {
            $entityManager->flush();
            $pageCategoryService->updatePageCategory();
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash(
                'error',
                'Nie można usunąć tej kategorii, ponieważ jest ona powiązana z innymi elementami.'
            );
        }

        $this->addFlash('notice', 'Kategoria została usunięta.');

        return $this->redirectToRoute('page-file', ['id' => $page->getId()]);
    }
}
