<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Link;
use App\Form\Type\LinkType;
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

#[Route("/link")]
final class LinkController extends AbstractController
{
    /**
     * @throws EntityNotFoundException
     */
    #[Route("/{page}", name: "link", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        EntityManagerInterface $entityManager,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $entityManager->getRepository(Link::class)->findAllByParams(
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
                return $this->redirectToRoute('link', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('link', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'link/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route("/create", name: "link-create")]
    #[Breadcrumb('Nowy link')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        MenuService $menuService,
        CacheItemPoolInterface $cacheItemPool
    ): Response {
        $link = new Link();

        $form = $this->createForm(LinkType::class, $link);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($link);
            $entityManager->flush();

            $this->addFlash('notice', 'Poprawnie dodano nowy link.');

            $cacheItemPool->deleteItem('accessArray');
            $menuService->rebuildRoles();

            return $this->redirectToRoute('link');
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
    #[Route("/{id}/update", name: "link-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb('Edycja linku')]
    public function update(
        Link $link,
        Request $request,
        EntityManagerInterface $entityManager,
        MenuService $menuService,
        CacheItemPoolInterface $cacheItemPool
    ): Response {
        $form = $this->createForm(LinkType::class, $link);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $menuService->updateMenu();

            $this->addFlash('notice', 'Zmiany zapisane.');

            $cacheItemPool->deleteItem('accessArray');
            $menuService->rebuildRoles();

            return $this->redirectToRoute('link');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $link->getName(),
            ]
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route("/{id}/delete", name: "link-delete", requirements: ["id" => "\d+"])]
    public function delete(
        Link $link,
        EntityManagerInterface $entityManager,
        MenuService $menuService,
        CacheItemPoolInterface $cacheItemPool
    ): RedirectResponse {
        $entityManager->remove($link);

        try {
            $entityManager->flush();
            $this->addFlash('notice', 'Link został skasowany.');
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Nie można usunąć tego linku, ponieważ jest on powiązany z innymi elementami.');
        }

        $cacheItemPool->deleteItem('accessArray');
        $menuService->rebuildRoles();

        return $this->redirectToRoute('link');
    }
}
