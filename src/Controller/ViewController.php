<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Category;
use App\Entity\File;
use App\Entity\Index;
use App\Entity\ISOCategory;
use App\Entity\ISOFile;
use App\Entity\ISOFileFileHistory;
use App\Entity\Page;
use App\Entity\PageCategory;
use App\Entity\PageFile;
use App\Entity\PageFileFileHistory;
use App\Entity\Special;
use App\Services\Component\ParameterBag;
use App\Services\PageService;
use App\Services\SpecialService;
use App\Services\TagService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

final class ViewController extends AbstractController
{
    #[Route("/page/{id}/view", name: "page-view", requirements: ["id" => "\d+"])]
    public function page(
        Page $page,
        Environment $twigEnvironment,
        EntityManagerInterface $entityManager,
        TagService $tagService,
    ): Response {
        if (!$page->getIsActive()) {
            throw $this->createNotFoundException('App\Entity\Page object not found.');
        }

        $rows = $entityManager->getRepository(PageCategory::class)->findAllByParams(
            new ParameterBag([
                'is-active' => true,
                'page-id' => $page->getId(),
            ])
        );

        $data = [
            'page' => $page,
            'rows' => $rows,
            'tags' => $tagService->getTags($rows),
        ];

        if ($page->getPageTemplate()) {
            $data['html'] = $twigEnvironment
                ->createTemplate($page->getPageTemplate()->getTemplate())
                ->render($data);
        }

        return $this->render('view/page.html.twig', $data);
    }

    /**
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route("/category/{id}/view", name: "category-view", requirements: ["id" => "\d+"])]
    public function category(Category $category, PageService $pageService, Environment $twigEnvironment): Response
    {
        if (!$category->getIsActive()) {
            throw $this->createNotFoundException('App\Entity\Category object not found.');
        }

        if ($category->getLink()) {
            return $this->redirectToRoute($category->getLink()->getName());
        }

        if ($category->getPage()) {
            return $this->redirectToRoute('page-view', ['id' => $category->getPage()->getId()]);
        }

        if ($category->getUrl()) {
            return $this->redirect($category->getUrl());
        }

        $data = [
            'category' => $category,
            'pages' => $pageService->findPagesByCategory($category),
        ];

        if ($category->getCategoryTemplate()) {
            $data['html'] = $twigEnvironment
                ->createTemplate($category->getCategoryTemplate()->getTemplate())
                ->render($data);
        }

        return $this->render('view/category.html.twig', $data);
    }

    #[Route("/search/{page}", name: "search", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function search(
        Request $request,
        int $page,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag
    ): Response {
        $rows = $entityManager->getRepository(Index::class)->findAllByParams(
            new ParameterBag(
                [
                    'query' => $request->get('query'),
                    'page' => $page,
                    'rows_per_page' => $parameterBag->get('rows_per_page'),
                ]
            )
        );
        $rowsCount = count($rows);

        if ($rowsCount) {
            if (!$page) {
                return $this->redirectToRoute('search', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / 20
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('search', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render('view/search.html.twig', [
            'query' => $request->query->get('query'),
            'rows' => $rows,
            'row_count' => count($rows),
        ]);
    }

    #[Route("/file/{id}/download", name: "file-download", requirements: ["id" => "\d+"])]
    public function fileDownload(File $file): Response
    {
        return $this->file($file->getAbsolutePath(), $file->getOriginalFilename());
    }

    #[Breadcrumb('Zintegrowany System Zarządzania Jakością (ZSZ)')]
    #[Route("/iso", name: "iso")]
    public function iso(
        EntityManagerInterface $entityManager,
        SpecialService $specialService,
        TagService $tagService,
    ): Response {
        $rows = $entityManager->getRepository(ISOCategory::class)->findAllByParams(
            new ParameterBag(['is-active' => true])
        );

        return $this->render('view/iso.html.twig', [
            'rows' => $rows,
            'special' => $specialService->getSpecial(Special::ISO),
            'tags' => $tagService->getTags($rows),
        ]);
    }

    #[Route("/iso/{id}/download", name: "iso-download", requirements: ["id" => "\d+"])]
    public function isoFileDownload(ISOFile $ISOFile): Response
    {
        return $this->file($ISOFile->getCurrentFileAbsolutePath(), $ISOFile->getCurrentFileOriginalFilename());
    }

    #[Route("/iso/{id}/download/archive", name: "iso-download-archive", requirements: ["id" => "\d+"])]
    public function isoFileFileHistoryDownload(ISOFileFileHistory $ISOFileFileHistory): Response
    {
        return $this->file($ISOFileFileHistory->getTemporaryAbsolutePath(), $ISOFileFileHistory->getFilename());
    }

    #[Route("/page/file/{id}/download", name: "page-file-download", requirements: ["id" => "\d+"])]
    public function pageFileDownload(PageFile $pageFile): Response
    {
        return $this->file($pageFile->getCurrentFileAbsolutePath(), $pageFile->getCurrentFileOriginalFilename());
    }

    #[Route("/page/file/{id}/download/archive", name: "page-file-download-archive", requirements: ["id" => "\d+"])]
    public function pageFileHistoryDownload(PageFileFileHistory $pageFileFileHistory): Response
    {
        return $this->file($pageFileFileHistory->getTemporaryAbsolutePath(), $pageFileFileHistory->getFilename());
    }
}
