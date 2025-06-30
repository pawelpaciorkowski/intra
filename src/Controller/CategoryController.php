<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Category;
use App\Form\Type\CategoryType;
use App\Services\CacheService;
use App\Services\CategoryService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/category")] final class CategoryController extends AbstractController
{
    #[Route("", name: "category")]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $rows = $entityManager->getRepository(Category::class)->findAllByParams();

        return $this->render(
            'category/list.html.twig',
            [
                'rows' => $rows,
            ]
        );
    }

    #[Route("/create", name: "category-create")]
    #[Breadcrumb("Nowa kategoria")]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        CategoryService $categoryService,
        CacheService $cacheService
    ): Response {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $categoryService->updateCategory();

            $this->addFlash('notice', 'Poprawnie dodano kategorię.');

            $cacheService->removeFromCacheByTag('category');

            return $this->redirectToRoute('category');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route("/{id}/update", name: "category-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Edycja kategorii")]
    public function update(
        Category $category,
        Request $request,
        EntityManagerInterface $entityManager,
        CategoryService $categoryService,
        CacheService $cacheService
    ): Response {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $categoryService->updateCategory();

            $this->addFlash('notice', 'Zmiany zapisane.');

            $cacheService->removeFromCacheByTag('category');

            return $this->redirectToRoute('category');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $category->getName(),
            ]
        );
    }

    #[Route("/{id}/delete", name: "category-delete", requirements: ["id" => "\d+"])]
    public function delete(
        Category $category,
        EntityManagerInterface $entityManager,
        CategoryService $categoryService,
        CacheService $cacheService
    ): RedirectResponse {
        $entityManager->remove($category);

        try {
            $entityManager->flush();
            $categoryService->updateCategory();
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash(
                'error',
                'Nie można usunąć tej kategorii, ponieważ jest ona powiązana z innymi elementami.'
            );
        }

        $this->addFlash('notice', 'Kategoria została usunięta.');

        $cacheService->removeFromCacheByTag('category');

        return $this->redirectToRoute('category');
    }
}
