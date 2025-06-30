<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Template;
use App\Form\Type\TemplateType;
use App\Services\RequestService;
use App\Services\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\Component\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function array_merge;
use function ceil;
use function count;

#[Route("/template")] final class TemplateController extends AbstractController
{
    #[Route("/{page}", name: "template", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        EntityManagerInterface $entityManager,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $entityManager->getRepository(Template::class)->findAllByParams(
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
                return $this->redirectToRoute('template', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('template', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'template/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    #[Route("/create", name: "template-create")]
    #[Breadcrumb("Nowy szablon")]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag
    ): Response {
        $template = new Template();

        $form = $this->createForm(
            TemplateType::class,
            $template,
            [
                'sender' => [
                    'name' => $parameterBag->get('email')['title_prefix'],
                    'email' => $parameterBag->get('email')['mailer_sender'],
                ],
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($template);
            $entityManager->flush();

            $this->addFlash('notice', 'Poprawnie dodano nowy szablon.');

            return $this->redirectToRoute('template');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route("/{id}/update", name: "template-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Edycja szablonu")]
    public function update(
        Template $template,
        Request $request,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag
    ): Response {
        $form = $this->createForm(
            TemplateType::class,
            $template,
            [
                'sender' => [
                    'name' => $parameterBag->get('email')['title_prefix'],
                    'email' => $parameterBag->get('email')['mailer_sender'],
                ],
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('template');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $template->getSubject(),
            ]
        );
    }

    #[Route("/{id}/delete", name: "template-delete", requirements: ["id" => "\d+"])]
    public function delete(Template $template, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($template);
        $entityManager->flush();

        $this->addFlash('notice', 'Szablon został skasowany.');

        return $this->redirectToRoute('template');
    }
}
