<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\CategoryTemplate;
use App\Form\Type\CategoryTemplateType;
use App\Services\Component\ParameterBag;
use App\Services\MenuService;
use App\Services\RequestService;
use App\Services\SettingService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function array_merge;
use function ceil;
use function count;

#[Route("/category-template")]
final class CategoryTemplateController extends AbstractController
{
    /**
     * @throws EntityNotFoundException
     */
    #[Route("/{page}", name: "category-template", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        EntityManagerInterface $entityManager,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $entityManager->getRepository(CategoryTemplate::class)->findAllByParams(
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
        $rowsCount = is_countable($rows) ? count($rows) : 0;

        if ($rowsCount) {
            if (!$page) {
                return $this->redirectToRoute('category-template', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('category-template', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'category-template/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route("/create", name: "category-template-create")]
    #[Breadcrumb('Nowy szablon kategorii')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        MenuService $menuService,
        CacheItemPoolInterface $cacheItemPool
    ): Response {
        $categoryTemplate = new CategoryTemplate();

        $form = $this->createForm(CategoryTemplateType::class, $categoryTemplate);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categoryTemplate);
            $entityManager->flush();

            $this->addFlash('notice', 'Poprawnie dodano nowy szablon kategorii.');

            $cacheItemPool->deleteItem('accessArray');
            $menuService->rebuildRoles();

            return $this->redirectToRoute('category-template');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route("/{id}/update", name: "category-template-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb('Edycja szablon kategorii')]
    public function update(
        CategoryTemplate $categoryTemplate,
        Request $request,
        EntityManagerInterface $entityManager,
        MenuService $menuService,
        CacheItemPoolInterface $cacheItemPool
    ): Response {
        $form = $this->createForm(CategoryTemplateType::class, $categoryTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $menuService->updateMenu();

            $this->addFlash('notice', 'Zmiany zapisane.');

            $cacheItemPool->deleteItem('accessArray');
            $menuService->rebuildRoles();

            return $this->redirectToRoute('category-template');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $categoryTemplate->getName(),
            ]
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route("/{id}/delete", name: "category-template-delete", requirements: ["id" => "\d+"])]
    public function delete(
        CategoryTemplate $categoryTemplate,
        EntityManagerInterface $entityManager,
        MenuService $menuService,
        CacheItemPoolInterface $cacheItemPool
    ): RedirectResponse {
        $entityManager->remove($categoryTemplate);

        try {
            $entityManager->flush();
            $this->addFlash('notice', 'CategoryTemplate został skasowany.');
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Nie można usunąć tego szablonu kategorii, ponieważ jest on powiązany z innymi elementami.');
        }

        $cacheItemPool->deleteItem('accessArray');
        $menuService->rebuildRoles();

        return $this->redirectToRoute('category-template');
    }
}
