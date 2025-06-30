<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\CollectionPoint;
use App\Entity\FileExport;
use App\Entity\Special;
use App\Services\CollectionPointService;
use App\Services\Component\ParameterBag;
use App\Services\Export\ExportService;
use App\Services\Export\Generator\Csv;
use App\Services\Export\Generator\Xlsx;
use App\Services\Export\Map\CollectionPointMap;
use App\Services\FileExportService;
use App\Services\RequestService;
use App\Services\SettingService;
use App\Services\SpecialService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface as SymfonyParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

use function array_merge;
use function ceil;
use function count;

#[Route("/collection-point")] final class CollectionPointController extends AbstractController
{
    #[Route("/{page}", name: "collection-point", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        RequestService $requestService,
        SettingService $settingService,
        CollectionPointService $collectionPointService
    ): Response {
        $filter = new ParameterBag(
            array_merge(
                $requestService->sortHandle()->getQuery(),
                [
                    'page' => $page,
                    'rows_per_page' => $settingService->getSetting('rows_per_page', $this->getUser()),
                ]
            )
        );

        if ($settingService->getSetting('collection_point_only_active', $this->getUser())) {
            $filter->add(['is-active' => true]);
        }

        $rows = $collectionPointService->findAllByParams($filter);
        $rowsCount = count($rows);

        if ($rowsCount) {
            if (!$page) {
                return $this->redirectToRoute('collection-point', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('collection-point', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'collection-point/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
                'isChildrenAgeOptions' => CollectionPoint::IS_CHILDREN_AGE_OPTIONS,
            ]
        );
    }

    #[Route("/{page}/public", name: "collection-point-public", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function listPublic(
        Request $request,
        int $page,
        RequestService $requestService,
        CollectionPointService $collectionPointService,
        SettingService $settingService,
        SymfonyParameterBagInterface $parameterBag,
        SpecialService $specialService
    ): Response {
        $rowsPerPage = $this->getUser() ? $settingService->getSetting('rows_per_page', $this->getUser()) : $parameterBag->get('rows_per_page');

        $filter = new ParameterBag(
            array_merge(
                $requestService->sortHandle('collection-point')->getQuery(),
                [
                    'is-active' => true,
                    'page' => $page,
                    'rows_per_page' => $rowsPerPage,
                ]
            )
        );

        $rows = $collectionPointService->findAllByParams($filter);
        $rowsCount = count($rows);

        if ($rowsCount) {
            if (!$page) {
                return $this->redirectToRoute('collection-point', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $rowsPerPage
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('collection-point', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'collection-point/list-public.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
                'isChildrenAgeOptions' => CollectionPoint::IS_CHILDREN_AGE_OPTIONS,
                'special' => $specialService->getSpecial(Special::COLLECTION_POINT_LIST),
            ]
        );
    }

    #[Route("/map", name: "collection-point-map", defaults: ["page" => 1])]
    public function map(SymfonyParameterBagInterface $parameterBag, CollectionPointService $collectionPointService): Response
    {
        return $this->render('collection-point/map.html.twig', [
            'mapboxToken' => $parameterBag->get('MAPBOX_TOKEN'),
            'collectionPoints' => $collectionPointService->findToView(),
        ]);
    }

    #[Route("/map/public", name: "collection-point-map-public", defaults: ["page" => 1])]
    public function mapPublic(
        SymfonyParameterBagInterface $parameterBag,
        CollectionPointService $collectionPointService,
        SpecialService $specialService
    ): Response {
        return $this->render('collection-point/map-public.html.twig', [
            'mapboxToken' => $parameterBag->get('MAPBOX_TOKEN'),
            'collectionPoints' => $collectionPointService->findToView(),
            'special' => $specialService->getSpecial(Special::COLLECTION_POINT_MAP),
        ]);
    }

    #[Route("/{id}/view", name: "collection-point-view", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Karta punktu pobrań")]
    public function view(int $id, CollectionPointService $collectionPointService): Response
    {
        $collectionPoint = $collectionPointService->findAllByParams(new ParameterBag(['id' => $id]));
        if (!$collectionPoint) {
            throw $this->createNotFoundException('App\Entity\CollectionPoint object not found.');
        }

        return $this->render(
            'collection-point/view.html.twig',
            [
                'collectionPoint' => $collectionPoint,
                'isChildrenAgeOptions' => CollectionPoint::IS_CHILDREN_AGE_OPTIONS,
            ]
        );
    }

    #[Route("/{id}/view/public", name: "collection-point-view-public", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Karta punktu pobrań")]
    public function viewPublic(int $id, CollectionPointService $collectionPointService): Response
    {
        $collectionPoint = $collectionPointService->findAllByParams(new ParameterBag(['id' => $id]));
        if (!$collectionPoint) {
            throw $this->createNotFoundException('App\Entity\CollectionPoint object not found.');
        }

        return $this->render(
            'collection-point/view.html.twig',
            [
                'collectionPoint' => $collectionPoint,
                'isChildrenAgeOptions' => CollectionPoint::IS_CHILDREN_AGE_OPTIONS,
            ]
        );
    }

    #[Route("/csv", name: "collection-point-csv")]
    public function csv(
        RequestService $requestService,
        CollectionPointService $collectionPointService,
        ExportService $exportService,
        SettingService $settingService,
        FileExportService $fileExportService
    ): StreamedResponse {
        $filter = new ParameterBag(
            $requestService->sortHandle('collection-point')->getQuery(),
        );

        if ($settingService->getSetting('collection_point_only_active', $this->getUser())) {
            $filter->add(['is-active' => true]);
        }

        $rows = $collectionPointService->findAllByParams($filter);

        return $exportService
            ->setMap(
                (new CollectionPointMap())->setColumns(
                    $fileExportService->getRowsForUser($this->getUser(), FileExport::COLLECTION_POINT, true)
                )
            )
            ->setData($rows)
            ->setGenerator(new Csv())
            ->export('collection-points');
    }

    #[Route("/xlsx", name: "collection-point-xlsx")]
    public function xlsx(
        RequestService $requestService,
        CollectionPointService $collectionPointService,
        ExportService $exportService,
        SettingService $settingService,
        FileExportService $fileExportService
    ): StreamedResponse {
        $filter = new ParameterBag(
            $requestService->sortHandle('collection-point')->getQuery(),
        );

        if ($settingService->getSetting('collection_point_only_active', $this->getUser())) {
            $filter->add(['is-active' => true]);
        }

        $rows = $collectionPointService->findAllByParams($filter);

        return $exportService
            ->setMap(
                (new CollectionPointMap())->setColumns(
                    $fileExportService->getRowsForUser($this->getUser(), FileExport::COLLECTION_POINT, true)
                )
            )
            ->setData($rows)
            ->setGenerator(new Xlsx())
            ->export('collection-points');
    }
}
