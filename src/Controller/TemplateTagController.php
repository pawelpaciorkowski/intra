<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\TemplateTag;
use App\Form\Type\TemplateTagType;
use App\Services\RequestService;
use App\Services\SettingService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\Component\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function array_merge;
use function ceil;
use function count;

#[Route("/template-tag")] final class TemplateTagController extends AbstractController
{
    #[Route("/{page}", name: "template-tag", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        EntityManagerInterface $entityManager,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $entityManager->getRepository(TemplateTag::class)->findAllByParams(
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
                return $this->redirectToRoute('template-tag', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('template-tag', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'template-tag/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    #[Route("/create", name: "template-tag-create")]
    #[Breadcrumb("Nowy wyzwalacz")]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $templateTag = new TemplateTag();

        $form = $this->createForm(TemplateTagType::class, $templateTag);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($templateTag);
            $entityManager->flush();

            $this->addFlash('notice', 'Poprawnie dodano nowy wyzwalacz.');

            return $this->redirectToRoute('template-tag');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route("/{id}/update", name: "template-tag-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Edycja wyzwalacza")]
    public function update(TemplateTag $templateTag, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TemplateTagType::class, $templateTag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('template-tag');
        }

        return $this->render(
            'partials/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $templateTag->getName(),
            ]
        );
    }

    #[Route("/{id}/delete", name: "template-tag-delete", requirements: ["id" => "\d+"])]
    public function delete(TemplateTag $templateTag, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($templateTag);

        try {
            $entityManager->flush();
            $this->addFlash('notice', 'Wyzwalacz został skasowany.');
        } /* @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash(
                'error',
                'Nie można usunąć tego wyzwalacza, ponieważ jest on powiązany z innymi elementami.'
            );
        }

        return $this->redirectToRoute('template-tag');
    }
}
