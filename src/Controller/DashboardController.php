<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\BoxService;
use App\Services\CarouselService;
use App\Services\PopupService;
use App\Services\SecurityService;
use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\Component\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function count;

final class DashboardController extends AbstractController
{
    #[Route("/", name: "index")]
    public function index(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->redirectToRoute('dashboard-public');
    }

    #[Route("/dashboard", name: "dashboard")]
    public function dashboard(
        SecurityService $securityService,
        UserService $userService
    ): Response {
        $viewParams = [];

        if ($securityService->hasAccess('user')) {
            $viewParams['user_count'] = count($userService->findAllByParams(new ParameterBag([
                'is-active' => true,
                'restrict' => true,
            ])));
        }

        return $this->render('dashboard/index.html.twig', $viewParams);
    }

    #[Route("/start", name: "dashboard-public")]
    public function dashboardPublic(BoxService $boxService, CarouselService $carouselService, PopupService $popupService): Response
    {
        $viewParams = [
            'boxes' => $boxService->findAllByParams(new ParameterBag(['is-active' => true])),
            'carousels' => $carouselService->findAllByParams(new ParameterBag(['is-active' => true])),
            'popup' => $popupService->findRandom(),
        ];

        return $this->render('dashboard/index-public.html.twig', $viewParams);
    }
}
