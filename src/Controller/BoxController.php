<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Box;
use App\Form\Type\BoxType;
use App\Services\BoxService;
use App\Services\Component\ParameterBag;
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

#[Route("/box")]
final class BoxController extends AbstractController
{
    #[Route("/{page}", name: "box", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        EntityManagerInterface $entityManager,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $entityManager->getRepository(Box::class)->findAllByParams(
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
                return $this->redirectToRoute('box', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('box', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'box/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    #[Route("/create", name: "box-create")]
    #[Breadcrumb("Nowy box")]
    public function create(Request $request, EntityManagerInterface $entityManager, BoxService $boxService): Response
    {
        $box = new Box();

        $form = $this->createForm(BoxType::class, $box);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($box);
            $entityManager->flush();

            $boxService->resort();

            $this->addFlash('notice', 'Poprawnie dodano nowy box.');

            return $this->redirectToRoute('box');
        }

        return $this->render(
            'box/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route("/{id}/update", name: "box-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Edycja boxu")]
    public function update(
        Box $box,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(BoxType::class, $box);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('box');
        }

        return $this->render(
            'box/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $box->getTitle(),
            ]
        );
    }

    #[Route("/{id}/delete", name: "box-delete", requirements: ["id" => "\d+"])]
    public function delete(
        Box $box,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $entityManager->remove($box);

        try {
            $entityManager->flush();
            $this->addFlash('notice', 'Box został skasowany.');
        } /* @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Nie można usunąć tego boxu, ponieważ jest on powiązany z innymi elementami.');
        }

        return $this->redirectToRoute('box');
    }
}
