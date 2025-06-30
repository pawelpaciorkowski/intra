<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Page;
use App\Entity\PageCategory;
use App\Form\Type\PageType;
use App\Security\Voter\PageVoter;
use App\Services\Component\ParameterBag;
use App\Services\MenuService;
use App\Services\PageService;
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

#[Route("/page")]
final class PageController extends AbstractController
{
    #[Route("/{page}", name: "page", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        RequestService $requestService,
        SettingService $settingService,
        PageService $pageService,
    ): Response {
        $rows = $pageService->findAllByParams(
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
                return $this->redirectToRoute('page', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('page', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'page/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    #[Breadcrumb('Nowa strona')]
    #[Route("/create", name: "page-create")]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $page = new Page();

        $form = $this->createForm(PageType::class, $page);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($page);
            $entityManager->flush();

            $this->addFlash('notice', 'Poprawnie dodano nową stronę.');

            return $this->redirectToRoute('page');
        }

        return $this->render(
            'page/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Breadcrumb('Edycja strony')]
    #[Route("/{id}/update", name: "page-update", requirements: ["id" => "\d+"])]
    public function update(
        Page $page,
        Request $request,
        EntityManagerInterface $entityManager,
        MenuService $menuService
    ): Response {
        $this->denyAccessUnlessGranted(PageVoter::UPDATE, $page);

        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $menuService->updateMenu();

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('page');
        }

        return $this->render(
            'page/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $page->getTitle(),
            ]
        );
    }

    #[Breadcrumb('Edycja plików')]
    #[Route("/{id}/page-file", name: "page-file", requirements: ["id" => "\d+"])]
    public function files(
        Page $page,
        EntityManagerInterface $entityManager,
    ): Response {
        $this->denyAccessUnlessGranted(PageVoter::UPDATE, $page);

        return $this->render(
            'page/file.html.twig',
            [
                'page' => $page,
                'subtitle' => $page->getTitle(),
                'rows' => $entityManager->getRepository(PageCategory::class)->findAllByParams(
                    new ParameterBag(
                        ['page-id' => $page->getId()]
                    )
                ),
            ]
        );
    }

    #[Route("/{id}/copy", name: "page-copy", requirements: ["id" => "\d+"])]
    public function copy(
        Page $page,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(PageVoter::DELETE, $page);

        $newPage = clone $page;
        $entityManager->persist($newPage);

        try {
            $entityManager->flush();
            $this->addFlash('notice', 'Strona została skopiowana.');
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Nie można skopiować tej strony.');
        }

        return $this->redirectToRoute('page');
    }

    #[Route("/{id}/delete", name: "page-delete", requirements: ["id" => "\d+"])]
    public function delete(
        Page $page,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(PageVoter::DELETE, $page);

        $entityManager->remove($page);

        try {
            $entityManager->flush();
            $this->addFlash('notice', 'Strona została skasowana.');
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Nie można usunąć tej strony, ponieważ jest ona powiązana z innymi elementami.');
        }

        return $this->redirectToRoute('page');
    }
}
