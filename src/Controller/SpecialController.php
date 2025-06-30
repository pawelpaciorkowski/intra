<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Special;
use App\Form\Type\SpecialType;
use App\Services\Component\ParameterBag;
use App\Services\RequestService;
use App\Services\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function array_merge;
use function ceil;
use function count;

#[Route("/special")]
final class SpecialController extends AbstractController
{
    #[Route("/{page}", name: "special", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        EntityManagerInterface $entityManager,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $entityManager->getRepository(Special::class)->findAllByParams(
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
                return $this->redirectToRoute('special', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('special', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'special/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    #[Route("/{id}/update", name: "special-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Edycja strony specjalnej")]
    public function update(Special $special, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SpecialType::class, $special);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('special');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $special->getTitle(),
            ]
        );
    }
}
