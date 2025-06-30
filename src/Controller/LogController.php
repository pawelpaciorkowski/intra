<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\LogDbTrackingService;
use App\Services\LogRequestService;
use App\Services\LogUserAuthService;
use App\Services\RequestService;
use App\Services\SettingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\Component\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function array_merge;
use function ceil;
use function count;

#[Route("/log")] final class LogController extends AbstractController
{
    #[Route("/in/{page}", name: "log-user-auth", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function logUserAuth(
        Request $request,
        int $page,
        RequestService $requestService,
        SettingService $settingService,
        LogUserAuthService $logUserAuthService
    ): Response {
        $rows = $logUserAuthService->findAllByParams(
            new ParameterBag(
                array_merge(
                    $requestService->sortHandle()->getQuery(),
                    [
                        'restrict' => true,
                        'page' => $page,
                        'rows_per_page' => $settingService->getSetting('rows_per_page', $this->getUser()),
                    ]
                )
            )
        );
        $rowsCount = count($rows);

        if ($rowsCount) {
            if (!$page) {
                return $this->redirectToRoute('log-user-auth', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('log-user-auth', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'log/log_user_auth.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    #[Route("/db-tracking/{page}", name: "log-db-tracking", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function logDbTracking(
        Request $request,
        int $page,
        LogDbTrackingService $logDbTrackingService,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $logDbTrackingService->findAllByParams(
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
                return $this->redirectToRoute('log-db-tracking', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('log-db-tracking', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'log/log_db_tracking.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    #[Route("/request/{page}", name: "log-request", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function logRequest(
        Request $request,
        int $page,
        LogRequestService $logRequestService,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $logRequestService->findAllByParams(
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
                return $this->redirectToRoute('log-request', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('log-request', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'log/log_request.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }
}
