<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Menu;
use App\Form\Type\MenuType;
use App\Services\CacheService;
use App\Services\MenuService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/menu")] final class MenuController extends AbstractController
{
    #[Route("", name: "menu")]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $rows = $entityManager->getRepository(Menu::class)->findAllByParams();

        return $this->render(
            'menu/list.html.twig',
            [
                'rows' => $rows,
            ]
        );
    }

    #[Breadcrumb('Nowy element menu')]
    #[Route("/create", name: "menu-create")]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        MenuService $menuService,
        CacheService $cacheService
    ): Response {
        $menu = new Menu();

        $form = $this->createForm(MenuType::class, $menu);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($menu);
            $entityManager->flush();

            $menuService->updateMenu();

            $this->addFlash('notice', 'Poprawnie dodano pozycję menu.');

            $menuService->rebuildRoles();
            $cacheService->removeFromCacheByTag('menu');

            return $this->redirectToRoute('menu');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Breadcrumb('Edycja elementu menu')]
    #[Route("/{id}/update", name: "menu-update", requirements: ["id" => "\d+"])]
    public function update(
        Menu $menu,
        Request $request,
        EntityManagerInterface $entityManager,
        MenuService $menuService,
        CacheService $cacheService
    ): Response {
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $menuService->updateMenu();

            $this->addFlash('notice', 'Zmiany zapisane.');

            $menuService->rebuildRoles();
            $cacheService->removeFromCacheByTag('menu');

            return $this->redirectToRoute('menu');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $menu->getName(),
            ]
        );
    }

    #[Route("/{id}/delete", name: "menu-delete", requirements: ["id" => "\d+"])]
    public function delete(
        Menu $menu,
        EntityManagerInterface $entityManager,
        MenuService $menuService,
        CacheService $cacheService
    ): RedirectResponse {
        $entityManager->remove($menu);

        try {
            $entityManager->flush();
            $menuService->updateMenu();
        } /* @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Nie można usunąć tej pozycji, ponieważ jest ona powiązana z innymi elementami.');
        }

        $this->addFlash('notice', 'Element menu został usunięty.');

        $menuService->rebuildRoles();
        $cacheService->removeFromCacheByTag('menu');

        return $this->redirectToRoute('menu');
    }
}
