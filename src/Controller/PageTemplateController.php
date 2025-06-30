<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\PageTemplate;
use App\Form\Type\PageTemplateType;
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

#[Route("/page-template")]
final class PageTemplateController extends AbstractController
{
    /**
     * @throws EntityNotFoundException
     */
    #[Route("/{page}", name: "page-template", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        EntityManagerInterface $entityManager,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $entityManager->getRepository(PageTemplate::class)->findAllByParams(
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
                return $this->redirectToRoute('page-template', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('page-template', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'page-template/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route("/create", name: "page-template-create")]
    #[Breadcrumb('Nowy szablon strony')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        MenuService $menuService,
        CacheItemPoolInterface $cacheItemPool
    ): Response {
        $pageTemplate = new PageTemplate();

        $form = $this->createForm(PageTemplateType::class, $pageTemplate);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($pageTemplate);
            $entityManager->flush();

            $this->addFlash('notice', 'Poprawnie dodano nowy szablon strony.');

            $cacheItemPool->deleteItem('accessArray');
            $menuService->rebuildRoles();

            return $this->redirectToRoute('page-template');
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
    #[Route("/{id}/update", name: "page-template-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb('Edycja szablonu strony')]
    public function update(
        PageTemplate $pageTemplate,
        Request $request,
        EntityManagerInterface $entityManager,
        MenuService $menuService,
        CacheItemPoolInterface $cacheItemPool
    ): Response {
        $form = $this->createForm(PageTemplateType::class, $pageTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $menuService->updateMenu();

            $this->addFlash('notice', 'Zmiany zapisane.');

            $cacheItemPool->deleteItem('accessArray');
            $menuService->rebuildRoles();

            return $this->redirectToRoute('page-template');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $pageTemplate->getName(),
            ]
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route("/{id}/delete", name: "page-template-delete", requirements: ["id" => "\d+"])]
    public function delete(
        PageTemplate $pageTemplate,
        EntityManagerInterface $entityManager,
        MenuService $menuService,
        CacheItemPoolInterface $cacheItemPool
    ): RedirectResponse {
        $entityManager->remove($pageTemplate);

        try {
            $entityManager->flush();
            $this->addFlash('notice', 'PageTemplate został skasowany.');
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Nie można usunąć tego szablonu strony, ponieważ jest on powiązany z innymi elementami.');
        }

        $cacheItemPool->deleteItem('accessArray');
        $menuService->rebuildRoles();

        return $this->redirectToRoute('page-template');
    }
}
