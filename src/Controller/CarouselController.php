<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Carousel;
use App\Form\Type\CarouselType;
use App\Services\CarouselService;
use App\Services\Component\ParameterBag;
use App\Services\RequestService;
use App\Services\SettingService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function array_merge;
use function ceil;
use function count;

#[Route("/carousel")]
final class CarouselController extends AbstractController
{
    #[Route("/{page}", name: "carousel", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        EntityManagerInterface $entityManager,
        RequestService $requestService,
        SettingService $settingService
    ): Response {
        $rows = $entityManager->getRepository(Carousel::class)->findAllByParams(
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
                return $this->redirectToRoute('carousel', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('carousel', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'carousel/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    #[Route("/create", name: "carousel-create")]
    #[Breadcrumb("Nowy slajd")]
    public function create(Request $request, EntityManagerInterface $entityManager, CarouselService $carouselService): Response
    {
        $carousel = new Carousel();

        $form = $this->createForm(CarouselType::class, $carousel);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($carousel);
            $entityManager->flush();

            $carouselService->resort();

            $this->addFlash('notice', 'Poprawnie dodano nowy slajd.');

            return $this->redirectToRoute('carousel');
        }

        return $this->render(
            'carousel/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route("/{id}/update", name: "carousel-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Edycja slajdu")]
    public function update(
        Carousel $carousel,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(CarouselType::class, $carousel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('notice', 'Zmiany zapisane.');

            return $this->redirectToRoute('carousel');
        }

        return $this->render(
            'carousel/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $carousel->getTitle(),
            ]
        );
    }

    #[Route("/{id}/delete", name: "carousel-delete", requirements: ["id" => "\d+"])]
    public function delete(
        Carousel $carousel,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $entityManager->remove($carousel);

        try {
            $entityManager->flush();
            $this->addFlash('notice', 'Carousel został skasowany.');
        } /* @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Nie można usunąć tego slajdu, ponieważ jest on powiązany z innymi elementami.');
        }

        return $this->redirectToRoute('carousel');
    }
}
