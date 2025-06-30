<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Section;
use App\Form\Type\SectionType;
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

#[Route("/section")]
final class SectionController extends AbstractController
{
    #[Route("/{page}", name: "section", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        EntityManagerInterface $entityManager,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $entityManager->getRepository(Section::class)->findAllByParams(
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
                return $this->redirectToRoute('section', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('section', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'section/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    #[Route("/create", name: "section-create")]
    #[Breadcrumb("Nowa sekcja")]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $section = new Section();

        $form = $this->createForm(SectionType::class, $section);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($section);
            $entityManager->flush();

            $this->addFlash('notice', 'Poprawnie dodano nową sekcję.');

            return $this->redirectToRoute('section');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route("/{id}/update", name: "section-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Edycja sekcji")]
    public function update(Section $section, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('section');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $section->getName(),
            ]
        );
    }

    #[Route("/{id}/delete", name: "section-delete", requirements: ["id" => "\d+"])]
    public function delete(Section $section, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($section);

        try {
            $entityManager->flush();
            $this->addFlash('notice', 'Sekcja została skasowana.');
        } /* @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Nie można usunąć tej sekcji, ponieważ jest ona powiązana z innymi elementami.');
        }

        return $this->redirectToRoute('section');
    }
}
