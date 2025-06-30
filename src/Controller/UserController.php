<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Team;
use App\Entity\User;
use App\Event\EmailEvent;
use App\Form\Type\ProfileType;
use App\Form\Type\UserType;
use App\Services\RequestService;
use App\Services\SettingService;
use App\Services\TicketService;
use App\Services\UserService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\Component\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

use function array_merge;
use function ceil;
use function count;

#[Route("/user")] final class UserController extends AbstractController
{
    #[Route("/{page}", name: "user", requirements: ["page" => "\d+"], defaults: ["page" => 1])]
    public function list(
        Request $request,
        int $page,
        RequestService $requestService,
        SettingService $settingService,
        UserService $userService
    ): Response {
        $filter = new ParameterBag(
            array_merge(
                $requestService->sortHandle()->getQuery(),
                [
                    'restrict' => true,
                    'page' => $page,
                    'rows_per_page' => $settingService->getSetting('rows_per_page', $this->getUser()),
                ]
            )
        );

        if ($settingService->getSetting('user_only_active', $this->getUser())) {
            $filter->add(['is-active' => true]);
        }

        $rows = $userService->findAllByParams($filter);
        $rowsCount = count($rows);

        if ($rowsCount) {
            if (!$page) {
                return $this->redirectToRoute('user', $request->query->all() + ['page' => 1]);
            }

            $maxPage = ceil(
                $rowsCount / $settingService->getSetting('rows_per_page', $this->getUser())
            );
            if ($page > $maxPage) {
                return $this->redirectToRoute('user', $request->query->all() + ['page' => $maxPage]);
            }
        }

        return $this->render(
            'user/list.html.twig',
            [
                'rows' => $rows,
                'row_count' => $rowsCount,
            ]
        );
    }

    #[Route("/create", name: "user-create")]
    #[Breadcrumb("Nowy użytkownik")]
    public function create(Request $request, UserService $userService): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userService->save($user, $form->get('password')->getData());

            $this->addFlash('notice', 'Poprawnie dodano nowego użytkownika.');

            return $this->redirectToRoute('user');
        }

        return $this->render(
            'user/form.html.twig',
            [
                'form' => $form->createView(),
                'regionTeamIds' => [Team::REGION_MANAGER_ID],
            ]
        );
    }

    #[Route("/{id}/update", name: "user-update", requirements: ["id" => "\d+"])]
    #[Breadcrumb("Edycja użytkownika")]
    public function update(
        int $id,
        Request $request,
        UserService $userService,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $userService->findAllByParams(new ParameterBag(['id' => $id, 'restrict' => true]));
        if (!$user) {
            throw $this->createNotFoundException('App\Entity\User object not found.');
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userService->save($user, $form->get('password')->getData());

            $this->addFlash('notice', 'Zmiany zapisane.');

            $lastRoute = $request->getSession()->get('last_route');
            if ($lastRoute && in_array($lastRoute['name'], ['lab-view', 'laboratory-view'], true)) {
                return $this->redirectToRoute($lastRoute['name'], $lastRoute['params']);
            }

            return $this->redirectToRoute('user');
        }

        if ($request->isMethod('POST')) {
            $entityManager->refresh($user);
        }

        return $this->render(
            'user/form.html.twig',
            [
                'form' => $form->createView(),
                'subtitle' => $user->getFullname(),
                'regionTeamIds' => [Team::REGION_MANAGER_ID],
            ]
        );
    }

    #[Route("/{id}/delete", name: "user-delete", requirements: ["id" => "\d+"])]
    public function delete(int $id, EntityManagerInterface $entityManager, UserService $userService): RedirectResponse
    {
        $user = $userService->findAllByParams(new ParameterBag(['id' => $id, 'restrict' => true]));
        if (!$user) {
            throw $this->createNotFoundException('App\Entity\User object not found.');
        }

        $entityManager->remove($user);

        try {
            $entityManager->flush();
            $this->addFlash('notice', 'Użytkownik poprawnie usunięty.');
        } /* @noinspection PhpRedundantCatchClauseInspection */ catch (ForeignKeyConstraintViolationException) {
            $this->addFlash(
                'error',
                'Nie można usunąć tego użytkownika, ponieważ jest on powiązany z innymi elementami.'
            );
        }

        return $this->redirectToRoute('user');
    }

    #[Route("/profile", name: "user-profile")]
    public function profile(
        Request $request,
        UserInterface $user,
        UserService $userService,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(ProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userService->setUserPassword($user, $form->get('newPassword')->getData());
            $entityManager->flush();
            $this->addFlash('notice', 'Zmiany zapisane.');
        } elseif ($request->isMethod('POST')) {
            $entityManager->refresh($user);
        }

        return $this->render(
            'user/profile.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route("/{id}/new-password", name: "user-new-password", requirements: ["id" => "\d+"])]
    public function newPassword(
        int $id,
        UserService $userService,
        TicketService $ticketService,
        EventDispatcherInterface $eventDispatcher
    ): RedirectResponse {
        $user = $userService->findAllByParams(new ParameterBag(['id' => $id, 'restrict' => true]));
        if (!$user) {
            throw $this->createNotFoundException('App\Entity\User object not found.');
        }

        if (!$user->getIsActive()) {
            $this->addFlash('error', 'Konto jest nieaktywne.');
        } else {
            $emailEvent = new EmailEvent(
                'new-password',
                [
                    'user' => $user,
                    'ticket' => $ticketService->generateTicketForUser($user),
                ]
            );
            $eventDispatcher->dispatch($emailEvent);

            $this->addFlash('notice', 'Do użytkownika wysłano e-mail z instrukcją.');
        }

        return $this->redirectToRoute('user-update', ['id' => $id]);
    }

    #[Route("/{id}/active/{status}", name: "user-active", requirements: ["id" => "\d+", "status" => "0|1"])]
    public function active(
        int $id,
        bool $status,
        EntityManagerInterface $entityManager,
        UserService $userService
    ): RedirectResponse {
        $user = $userService->findAllByParams(new ParameterBag(['id' => $id, 'restrict' => true]));
        if (!$user) {
            throw $this->createNotFoundException('App\Entity\User object not found.');
        }

        $user->setIsActive($status);
        $entityManager->flush();

        if ($status) {
            $this->addFlash('notice', 'Włączono użytkownika.');
        } else {
            $this->addFlash('notice', 'Wyłączono użytkownika.');
        }

        return $this->redirectToRoute('user');
    }
}
