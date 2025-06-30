<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Services\DepartmentService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function json_decode;

final class ApiDepartmentController extends AbstractFOSRestController
{
    #[Route("/api/department/order", name: "api-patch-department-order")]
    public function patchOrderAction(Request $request, DepartmentService $departmentService): View
    {
        $data = json_decode($request->request->get('tree'), false);

        if ($departmentService->setOrder($data)) {
            return View::create(null, Response::HTTP_NO_CONTENT);
        }

        return View::create(['message' => 'wrong data provided'], Response::HTTP_BAD_REQUEST);
    }
}
