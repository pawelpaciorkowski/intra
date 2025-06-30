<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Team;
use App\Form\Type\TeamType;
use App\Services\RequestService;
use App\Services\SettingService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\Component\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function array_merge;
use function ceil;
use function count;

#[Route("/team")] final class TeamController extends AbstractController
{
    #[Route("/{page}", name: "team", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        EntityManagerInterface $entityManager,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $entityManager->getRepository(Team::class)->findAllByParams(
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
                return $this->redirectToRoute('team', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('team', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'team/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    #[Route("/create", name: "team-create")]
    #[Breadcrumb("Nowa grupa")]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $team = new Team();

        $form = $this->createForm(TeamType::class, $team);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($team);
            $entityManager->flush();

            $this->addFlash('notice', 'Poprawnie dodano nową grupę.');

            return $this->redirectToRoute('team');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route("/{id}/update", name: "team-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Edycja grupy")]
    public function update(Team $team, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('team');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $team->getName(),
            ]
        );
    }

    #[Route("/{id}/delete", name: "team-delete", requirements: ["id" => "\d+"])]
    public function delete(Team $team, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($team);

        try {
            $entityManager->flush();
            $this->addFlash('notice', 'Grupa została skasowana.');
        } /* @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Nie można usunąć tej grupy, ponieważ jest ona powiązana z innymi elementami.');
        }

        return $this->redirectToRoute('team');
    }
}
