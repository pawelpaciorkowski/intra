<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Popup;
use App\Form\Type\PopupType;
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

#[Route("/popup")]
final class PopupController extends AbstractController
{
    #[Route("/{page}", name: "popup", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        EntityManagerInterface $entityManager,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $entityManager->getRepository(Popup::class)->findAllByParams(
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
                return $this->redirectToRoute('popup', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('popup', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'popup/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    #[Route("/create", name: "popup-create")]
    #[Breadcrumb("Nowy popup")]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $popup = new Popup();

        $form = $this->createForm(PopupType::class, $popup);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($popup);
            $entityManager->flush();

            $this->addFlash('notice', 'Poprawnie dodano nowy popup.');

            return $this->redirectToRoute('popup');
        }

        return $this->render(
            'popup/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route("/{id}/update", name: "popup-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Edycja popupu")]
    public function update(
        Popup $popup,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(PopupType::class, $popup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('popup');
        }

        return $this->render(
            'popup/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $popup->getTitle(),
            ]
        );
    }

    #[Route("/{id}/delete", name: "popup-delete", requirements: ["id" => "\d+"])]
    public function delete(
        Popup $popup,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $entityManager->remove($popup);

        try {
            $entityManager->flush();
            $this->addFlash('notice', 'Popup został skasowany.');
        } /* @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Nie można usunąć tego popupu, ponieważ jest on powiązany z innymi elementami.');
        }

        return $this->redirectToRoute('popup');
    }
}
