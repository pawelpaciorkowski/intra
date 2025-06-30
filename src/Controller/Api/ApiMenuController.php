<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Services\CacheService;
use App\Services\MenuService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\View\View;
use JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function json_decode;

final class ApiMenuController extends AbstractFOSRestController
{
    /**
     * @throws JsonException
     */
    #[Patch("/api/menu/order", name: "api-patch-menu-order", options: ["method_prefix" => false])]
    public function patchOrderAction(Request $request, MenuService $menuService, CacheService $cacheService): View
    {
        $data = json_decode($request->request->get('tree'), false, 512, JSON_THROW_ON_ERROR);

        if ($menuService->setOrder($data)) {
            $cacheService->removeFromCacheByTag('menu');

            return View::create(null, Response::HTTP_NO_CONTENT);
        }

        return View::create(['message' => 'wrong data provided'], Response::HTTP_BAD_REQUEST);
    }
}
