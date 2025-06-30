<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Services\CacheService;
use App\Services\PageCategoryService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function json_decode;

final class ApiPageCategoryController extends AbstractFOSRestController
{
    #[Patch("/api/page-category/order", name: "api-patch-page-category-order", options: ["method_prefix" => false])]
    public function patchOrderAction(
        Request $request,
        PageCategoryService $pageCategoryService,
        CacheService $cacheService
    ): View {
        $data = json_decode($request->request->get('tree'), false);

        if ($pageCategoryService->setOrder($data)) {
            return View::create(null, Response::HTTP_NO_CONTENT);
        }

        return View::create(['message' => 'wrong data provided'], Response::HTTP_BAD_REQUEST);
    }
}
