<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Services\SettingService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

final class ApiSettingController extends AbstractFOSRestController
{
    #[Patch("/api/setting", name: "api-patch-setting", options: ["method_prefix" => false])]
    public function patchSettingAction(Request $request, SettingService $settingService): View
    {
        $settingService->saveSettings($request->request->all(), $this->getUser());

        return View::create(['status' => true]);
    }
}
