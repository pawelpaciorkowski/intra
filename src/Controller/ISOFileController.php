<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\ISOCategory;
use App\Entity\ISOFileFileHistory;
use App\Entity\ISOFile;
use App\Form\Type\ISOFileType;
use App\Repository\TagRepository;
use App\Services\ISOFileFileHistoryService;
use App\Services\ISOFileService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/iso-file")] final class ISOFileController extends AbstractController
{
    #[Route("/create", name: "iso-file-create")]
    #[Breadcrumb("Nowy plik")]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ISOFileService $ISOFileService,
        ISOFileFileHistoryService $ISOFileFileHistoryService,
        TagRepository $tagRepository,
    ): Response {
        $ISOFile = new ISOFile();

        if ($request->query->has('iso-category-id')) {
            $ISOFile->setISOCategory($entityManager->getRepository(ISOCategory::class)->find($request->query->get('iso-category-id')));
        }

        $form = $this->createForm(ISOFileType::class, $ISOFile);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ISOFile->setTags($tagRepository->getTags($form->get('tags')->getData()));
            $entityManager->persist($ISOFile);
            $entityManager->flush();

            $ISOFileService->resort($ISOFile->getISOCategory());

            if ($form->get('currentFile')->getData()) {
                $ISOFileFileHistoryService->saveCurrent($ISOFile);
            }

            if ($form->get('originalFile')->getData()) {
                $ISOFileFileHistoryService->saveOriginal($ISOFile);
            }

            $this->addFlash('notice', 'Poprawnie dodano plik.');

            return $this->redirectToRoute('iso-category');
        }

        return $this->render(
            'iso-file/form.html.twig',
            [
                'form' => $form->createView(),
                'tags' => $tagRepository->findBy([], ['name' => 'ASC']),
            ]
        );
    }

    #[Route("/{id}/update", name: "iso-file-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Edycja pliku")]
    public function update(
        ISOFile $ISOFile,
        Request $request,
        EntityManagerInterface $entityManager,
        ISOFileFileHistoryService $ISOFileFileHistoryService,
        TagRepository $tagRepository,
    ): Response {
        $form = $this->createForm(ISOFileType::class, $ISOFile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ISOFile->setTags($tagRepository->getTags($form->get('tags')->getData()));
            $entityManager->flush();

            if ($form->get('currentFile')->getData()) {
                $ISOFileFileHistoryService->saveCurrent($ISOFile);
            }

            if ($form->get('originalFile')->getData()) {
                $ISOFileFileHistoryService->saveOriginal($ISOFile);
            }

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('iso-category');
        }

        return $this->render(
            'iso-file/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $ISOFile->getName(),
                'ISOFileFileHistory' => $ISOFile->getISOFileFileHistory(),
                'tags' => $tagRepository->findBy([], ['name' => 'ASC']),
            ]
        );
    }

    #[Route("/{id}/delete", name: "iso-file-delete", requirements: ["id" => "\d+"])]
    public function delete(
        ISOFile $ISOFile,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $entityManager->remove($ISOFile);

        try {
            $entityManager->flush();
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash(
                'error',
                'Nie można usunąć tego pliku, ponieważ jest on powiązany z innymi elementami.'
            );
        }

        $this->addFlash('notice', 'Plik został usunięty.');

        return $this->redirectToRoute('iso-category');
    }

    #[Route("/{id}/download/original", name: "iso-file-download-original", requirements: ["id" => "\d+"])]
    public function downloadOriginal(ISOFile $ISOFile): BinaryFileResponse
    {
        return $this->file($ISOFile->getOriginalFileAbsolutePath(), $ISOFile->getCurrentFileOriginalFilename());
    }

    #[Route("/{id}/download/current", name: "iso-file-download-current", requirements: ["id" => "\d+"])]
    public function downloadCurrent(ISOFile $ISOFile): BinaryFileResponse
    {
        return $this->file($ISOFile->getCurrentFileAbsolutePath(), $ISOFile->getCurrentFileOriginalFilename());
    }

    #[Route("/{id}/download/archive", name: "iso-file-file-history-download", requirements: ["id" => "\d+"])]
    public function downloadArchive(ISOFileFileHistory $ISOFileFileHistory): BinaryFileResponse
    {
        return $this->file($ISOFileFileHistory->getTemporaryAbsolutePath(), $ISOFileFileHistory->getFilename());
    }
}
