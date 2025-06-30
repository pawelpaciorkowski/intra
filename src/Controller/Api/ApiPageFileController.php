<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Services\PageFileService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApiPageFileController extends AbstractFOSRestController
{
    #[Patch("/api/page-file/order", name: "api-page-file-order", options: ["method_prefix" => false])]
    public function patchOrderAction(Request $request, PageFileService $pageFileService): View
    {
        if ($pageFileService->setSort($request->request->all('data'))) {
            return View::create(null, Response::HTTP_NO_CONTENT);
        }

        return View::create(['message' => 'wrong data provided'], Response::HTTP_BAD_REQUEST);
    }
}
