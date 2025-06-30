<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Page;
use App\Entity\PageCategory;
use App\Entity\PageFile;
use App\Entity\PageFileFileHistory;
use App\Form\Type\PageFileType;
use App\Repository\TagRepository;
use App\Services\PageFileFileHistoryService;
use App\Services\PageFileService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/page")]
final class PageFileController extends AbstractController
{
    #[Route("/{id}/page-file/create", name: "page-file-create", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Nowy plik")]
    public function create(
        Page $page,
        Request $request,
        EntityManagerInterface $entityManager,
        PageFileService $pageFileService,
        PageFileFileHistoryService $pageFileFileHistoryService,
        TagRepository $tagRepository,
    ): Response {
        $pageFile = new PageFile();

        if ($request->query->has('page-category-id')) {
            $pageFile->setPageCategory($entityManager->getRepository(PageCategory::class)->find($request->query->get('page-category-id')));
        }

        $form = $this->createForm(PageFileType::class, $pageFile, ['page' => $page]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $pageFile->setTags($tagRepository->getTags($form->get('tags')->getData()));
            $entityManager->persist($pageFile);
            $entityManager->flush();

            $pageFileService->resort($pageFile->getPageCategory());

            if ($form->get('currentFile')->getData()) {
                $pageFileFileHistoryService->saveCurrent($pageFile);
            }

            if ($form->get('originalFile')->getData()) {
                $pageFileFileHistoryService->saveOriginal($pageFile);
            }

            $this->addFlash('notice', 'Poprawnie dodano plik.');

            return $this->redirectToRoute('page-file', ['id' => $page->getId()]);
        }

        return $this->render(
            'page-file/form.html.twig',
            [
                'form' => $form->createView(),
                'tags' => $tagRepository->findBy([], ['name' => 'ASC']),
            ]
        );
    }

    #[Route("/{id}/page-file/{pageFile}/update", name: "page-file-update", requirements: ["id" => "\d+", "pageFile" => "\d+"])]
    #[Breadcrumb("Edycja pliku")]
    public function update(
        Page $page,
        PageFile $pageFile,
        Request $request,
        EntityManagerInterface $entityManager,
        PageFileFileHistoryService $pageFileFileHistoryService,
        TagRepository $tagRepository,
    ): Response {
        $form = $this->createForm(PageFileType::class, $pageFile, ['page' => $page]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pageFile->setTags($tagRepository->getTags($form->get('tags')->getData()));
            $entityManager->flush();

            if ($form->get('currentFile')->getData()) {
                $pageFileFileHistoryService->saveCurrent($pageFile);
            }

            if ($form->get('originalFile')->getData()) {
                $pageFileFileHistoryService->saveOriginal($pageFile);
            }

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('page-file', ['id' => $page->getId()]);
        }

        return $this->render(
            'page-file/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $pageFile->getName(),
                'PageFileFileHistory' => $pageFile->getPageFileFileHistory(),
                'tags' => $tagRepository->findBy([], ['name' => 'ASC']),
            ]
        );
    }

    #[Route("/{id}/page-file/{pageFile}/delete", name: "page-file-delete", requirements: ["id" => "\d+", "pageFile" => "\d+"])]
    public function delete(
        Page $page,
        PageFile $pageFile,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $entityManager->remove($pageFile);

        try {
            $entityManager->flush();
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash(
                'error',
                'Nie można usunąć tego pliku, ponieważ jest on powiązany z innymi elementami.'
            );
        }

        $this->addFlash('notice', 'Plik został usunięty.');

        return $this->redirectToRoute('page-file', ['id' => $page->getId()]);
    }

    #[Route("/{id}/download/original", name: "page-file-download-original", requirements: ["id" => "\d+"])]
    public function downloadOriginal(PageFile $pageFile): BinaryFileResponse
    {
        return $this->file($pageFile->getOriginalFileAbsolutePath(), $pageFile->getCurrentFileOriginalFilename());
    }

    #[Route("/{id}/download/current", name: "page-file-download-current", requirements: ["id" => "\d+"])]
    public function downloadCurrent(PageFile $pageFile): BinaryFileResponse
    {
        return $this->file($pageFile->getCurrentFileAbsolutePath(), $pageFile->getCurrentFileOriginalFilename());
    }

    #[Route("/{id}/download/archive", name: "page-file-file-history-download", requirements: ["id" => "\d+"])]
    public function downloadArchive(PageFileFileHistory $pageFileFileHistory): BinaryFileResponse
    {
        return $this->file($pageFileFileHistory->getTemporaryAbsolutePath(), $pageFileFileHistory->getFilename());
    }
}
