<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Services\CarouselService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApiCarouselController extends AbstractFOSRestController
{
    #[Patch("/api/carousel/order", name: "api-carousel-order", options: ["method_prefix" => false])]
    public function patchOrderAction(Request $request, CarouselService $carouselService): View
    {
        if ($carouselService->setSort($request->request->all('data'))) {
            return View::create(null, Response::HTTP_NO_CONTENT);
        }

        return View::create(['message' => 'wrong data provided'], Response::HTTP_BAD_REQUEST);
    }
}
