<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Services\CacheService;
use App\Services\CategoryService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function json_decode;

final class ApiCategoryController extends AbstractFOSRestController
{
    #[Patch("/api/category/order", name: "api-patch-category-order", options: ["method_prefix" => false])]
    public function patchOrderAction(
        Request $request,
        CategoryService $categoryService,
        CacheService $cacheService
    ): View {
        $data = json_decode($request->request->get('tree'), false);

        if ($categoryService->setOrder($data)) {
            $cacheService->removeFromCacheByTag('category');

            return View::create(null, Response::HTTP_NO_CONTENT);
        }

        return View::create(['message' => 'wrong data provided'], Response::HTTP_BAD_REQUEST);
    }
}
