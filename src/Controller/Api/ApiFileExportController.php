<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\FileExport;
use App\Services\FileExportService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApiFileExportController extends AbstractFOSRestController
{
    #[Patch("/api/file-export", name: "api-file-export", options: ["method_prefix" => false])]
    public function patchOrderAction(Request $request, FileExportService $fileExportService): View
    {
        if ($fileExportService->setRows($request->request->all('data'), FileExport::COLLECTION_POINT)) {
            return View::create(null, Response::HTTP_NO_CONTENT);
        }

        return View::create(['message' => 'wrong data provided'], Response::HTTP_BAD_REQUEST);
    }
}
