<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\CollectionPoint;
use App\Entity\Special;
use App\Services\LaboratoryService;
use App\Services\RequestService;
use App\Services\SettingService;
use App\Services\SpecialService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\Component\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function array_merge;
use function ceil;
use function count;

#[Route("/laboratory")] final class LaboratoryController extends AbstractController
{
    #[Route("/{page}", name: "laboratory", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        LaboratoryService $laboratoryService,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $laboratoryService->findAllByParams(
            new ParameterBag(
                array_merge(
                    $requestService->sortHandle()->getQuery(),
                    [
                        'is-active' => true,
                        'page' => $page,
                        'rows_per_page' => $settingService->getSetting('rows_per_page', $this->getUser()),
                    ]
                )
            )
        );
        $rowsCount = count($rows);

        if ($rowsCount) {
            if (!$page) {
                return $this->redirectToRoute('laboratory', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting(
                    'rows_per_page',
                    $this->getUser()
                )
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('laboratory', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'laboratory/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    #[Route("/{page}/public", name: "laboratory-public", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function listPublic(
        Request $request,
        int $page,
        LaboratoryService $laboratoryService,
        RequestService $requestService,
        SettingService $settingService,
        ParameterBagInterface $parameterBag,
        SpecialService $specialService
    ): Response {
        $rowsPerPage = $this->getUser() ? $settingService->getSetting('rows_per_page', $this->getUser()) : $parameterBag->get('rows_per_page');

        $rows = $laboratoryService->findAllByParams(
            new ParameterBag(
                array_merge(
                    $requestService->sortHandle('laboratory')->getQuery(),
                    [
                        'is-active' => true,
                        'page' => $page,
                        'rows_per_page' => $rowsPerPage,
                    ]
                )
            )
        );
        $rowsCount = count($rows);

        if ($rowsCount) {
            if (!$page) {
                return $this->redirectToRoute('laboratory', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $rowsPerPage
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('laboratory', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'laboratory/list-public.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
                'special' => $specialService->getSpecial(Special::LABORATORY_LIST),
            ]
        );
    }

    #[Route("/{id}/view", name: "laboratory-view", requirements: ["id" => "\d+"])]
    #[Breadcrumb('Karta laboratorium')]
    public function view(int $id, LaboratoryService $laboratoryService): Response
    {
        $viewParams = [
            'laboratory' => $laboratoryService->findAllByParams(new ParameterBag([
                'id' => $id,
                'is-active' => true,
                'orderBy' => 'lg.name',
            ])),
            'isChildrenAgeOptions' => CollectionPoint::IS_CHILDREN_AGE_OPTIONS,
        ];

        if (!$viewParams['laboratory']) {
            throw $this->createNotFoundException('App\Entity\Laboratory object not found.');
        }

        $viewParams['from'] = (new DateTime('-30 day'))->setTime(0, 0);
        $viewParams['to'] = new DateTime();

        return $this->render('laboratory/view.html.twig', $viewParams);
    }

    #[Route("/{id}/view/public", name: "laboratory-view-public", requirements: ["id" => "\d+"])]
    #[Breadcrumb('Karta laboratorium')]
    public function viewPublic(int $id, LaboratoryService $laboratoryService): Response
    {
        $viewParams = [
            'laboratory' => $laboratoryService->findAllByParams(new ParameterBag([
                'id' => $id,
                'is-active' => true,
                'orderBy' => 'lg.name',
            ])),
            'isChildrenAgeOptions' => CollectionPoint::IS_CHILDREN_AGE_OPTIONS,
        ];

        if (!$viewParams['laboratory']) {
            throw $this->createNotFoundException('App\Entity\Laboratory object not found.');
        }

        $viewParams['from'] = (new DateTime('-30 day'))->setTime(0, 0);
        $viewParams['to'] = new DateTime();

        return $this->render('laboratory/view.html.twig', $viewParams);
    }
}
