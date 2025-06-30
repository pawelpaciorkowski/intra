<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Services\CacheService;
use App\Services\ISOCategoryService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function json_decode;

final class ApiIsoCategoryController extends AbstractFOSRestController
{
    #[Patch("/api/iso-category/order", name: "api-patch-iso-category-order", options: ["method_prefix" => false])]
    public function patchOrderAction(
        Request $request,
        ISOCategoryService $ISOCategoryService,
        CacheService $cacheService
    ): View {
        $data = json_decode($request->request->get('tree'), false);

        if ($ISOCategoryService->setOrder($data)) {
            return View::create(null, Response::HTTP_NO_CONTENT);
        }

        return View::create(['message' => 'wrong data provided'], Response::HTTP_BAD_REQUEST);
    }
}
