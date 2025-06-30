<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\FileExport;
use App\Entity\Setting;
use App\Form\Type\SettingType;
use App\Form\Type\UserSettingType;
use App\Services\FileExportService;
use App\Services\RequestService;
use App\Services\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\Component\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use function array_merge;
use function ceil;
use function count;

final class SettingController extends AbstractController
{
    #[Route("/setting/{page}", name: "setting", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        EntityManagerInterface $entityManager,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $entityManager->getRepository(Setting::class)->findAllByParams(
            new ParameterBag(
                array_merge(
                    $requestService->sortHandle()->getQuery(),
                    [
                        'page' => $page,
                        'rows_per_page' => $settingService->getSetting('rows_per_page', $this->getUser()),
                    ]
                )
            )
        );
        $rowsCount = count($rows);

        if ($rowsCount) {
            if (!$page) {
                return $this->redirectToRoute('setting', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('setting', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'setting/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    #[Breadcrumb('Nowe ustawienie')]
    #[Route("/setting/create", name: "setting-create")]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $setting = new Setting();

        $form = $this->createForm(SettingType::class, $setting);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($setting);
            $entityManager->flush();

            $this->addFlash('notice', 'Poprawnie dodano nowe ustawienie.');

            return $this->redirectToRoute('setting');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Breadcrumb('Edycja ustawienia')]
    #[Route("/setting/{id}/update", name: "setting-update", requirements: ["id" => "\d+"])]
    public function update(Setting $setting, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('setting');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $setting->getName(),
            ]
        );
    }

    #[Route("/setting/{id}/delete", name: "setting-delete", requirements: ["id" => "\d+"])]
    public function delete(Setting $setting, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($setting);
        $entityManager->flush();

        $this->addFlash('notice', 'Ustawienie zostało skasowane.');

        return $this->redirectToRoute('setting');
    }

    #[Route("/user/setting", name: "user-setting")]
    public function user(
        Request $request,
        TokenStorageInterface $tokenStorage,
        SettingService $settingService
    ): Response {
        $settings = $settingService->findAllByParams(
            new ParameterBag([
                'restrict' => true,
                'is-active' => 1,
                'rows_per_page' => 100,
                'page' => 1,
                'orderBy' => 'e.order asc, s.name asc',
            ]),
            $tokenStorage->getToken()->getUser()
        );
        $settingManager = $settingService;

        $form = $this->createForm(
            UserSettingType::class,
            $settingManager->getSettingsAsArray(),
            ['settings' => $settings]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $settingManager->saveSettings($form->getData(), $this->getUser());

            $this->addFlash('notice', 'Ustawienia zapisane.');

            return $this->redirectToRoute('user-setting');
        }

        return $this->render(
            'setting/user-setting.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route("/user/file-export", name: "user-file-export")]
    public function fileExport(FileExportService $fileExportService): Response
    {
        return $this->render(
            'setting/user-file-export.html.twig',
            [
                'rows' => $fileExportService->getRowsForUser(
                    $this->getUser(),
                    FileExport::COLLECTION_POINT
                ),
            ]
        );
    }
}
