<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\Type\NewPasswordType;
use App\Form\Type\PasswordRecoverType;
use App\Services\Exception\RecoverPasswordException;
use App\Services\PasswordHistoryService;
use App\Services\UserService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    private const DASHBOARD_ROUTE = 'dashboard';

    #[Route("/login", name: "login")]
    #[readcrumb("Logowanie")]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Logged user is redirected to dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute(self::DASHBOARD_ROUTE);
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
            ]
        );
    }

    #[Route("/password/recover", name: "password-recover")]
    #[Breadcrumb("Resetuj hasło")]
    public function recover(Request $request, UserService $userService): Response
    {
        $responseParams = [];

        $form = $this->createForm(PasswordRecoverType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $userService->recoverPasswordByUsernameOrEmail($data['username']);
                $responseParams['info'] = 'Odbierz pocztę z dalszymi instrukcjami';
            } catch (Exception | RecoverPasswordException $exception) {
                $responseParams['error'] = $exception->getMessage();
            }
        }

        $responseParams['form'] = $form;

        return $this->render('security/recover.html.twig', $responseParams);
    }

    #[Route("/new-password/{ticket}", name: "new-password", requirements: ["ticket" => "[a-z0-9]+"])]
    #[Breadcrumb("Nowe hasło")]
    public function newPassword(
        string $ticket,
        Request $request,
        UserService $userService,
        EntityManagerInterface $entityManager
    ): Response {
        $ticketEntity = $entityManager
            ->getRepository(Ticket::class)
            ->findOneBy(['ticket' => $ticket, 'type' => Ticket::TYPE_PASSWORD]);

        $responseParams = [];

        if ($ticketEntity) {
            if ($ticketEntity->getIsValid()) {
                $user = $ticketEntity->getUser();

                if ($user->getIsActive()) {
                    $user->clearPassword();

                    $form = $this->createForm(
                        NewPasswordType::class,
                        $user,
                        ['validation_groups' => ['recover_password']]
                    );
                    $responseParams['fullname'] = $user->getFullname();

                    $form->handleRequest($request);
                    if ($form->isSubmitted() && $form->isValid()) {
                        // set ticket invalid
                        $ticketEntity->setIsValid(false);

                        // save new password to user entity
                        $userService->setUserPassword($user, $form->get('password')->getData());
                        $entityManager->persist($user);
                        $entityManager->flush();

                        $this->addFlash('notice', 'Hasło zostało zmienione. Zaloguj się teraz używając nowego hasła.');

                        return $this->redirectToRoute(self::DASHBOARD_ROUTE);
                    }

                    $responseParams['form'] = $form->createView();
                } else {
                    $responseParams['error'] = 'Konto jest nieaktywne.';
                }
            } else {
                $responseParams['error'] = 'Podany link wygasł.';
            }
        } else {
            $responseParams['error'] = 'Podany link jest nieprawidłowy.';
        }

        return $this->render('security/new-password.html.twig', $responseParams);
    }

    #[Route("/password/change", name: "password-change")]
    #[Breadcrumb("Zmiana hasła")]
    public function passwordChange(
        Request $request,
        UserInterface $user,
        PasswordHistoryService $passwordHistoryService,
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        UserService $userService
    ): Response {
        if ($this->isGranted('ROLE_PREVIOUS_ADMIN')) {
            throw new AccessDeniedException('Only primary users can change password');
        }

        if (!$user->getIsPasswordChangeRequired()) {
            throw new AccessDeniedException('User has set permanent password');
        }

        // Password change date must exist and must be young
        $passwordChangeAt = new DateTime('-' . User::MAX_PASSWORD_AGE . ' seconds');
        $currentPasswordChangeAt = $user->getPasswordChangeAt();
        if ($currentPasswordChangeAt instanceof DateTime && $currentPasswordChangeAt > $passwordChangeAt) {
            throw new AccessDeniedException('Password was already changed lately');
        }

        $form = $this->createForm(NewPasswordType::class, $user, ['validation_groups' => ['recover_password']]);

        $responseParams = [];

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($passwordHistoryService->exists($this->getUser(), $form->get('password')->getData())) {
                $responseParams['error'] = 'Wpisane hasło było już kiedyś używane.';
            } else {
                // save new password to user entity
                $userService->setUserPassword($user, $form->get('password')->getData());
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('notice', 'Hasło zostało zmienione');

                if ($session->has('password-change-url')) {
                    return $this->redirect($session->get('password-change-url'));
                }

                return $this->redirectToRoute(self::DASHBOARD_ROUTE);
            }
        }
        $entityManager->refresh($this->getUser());

        $responseParams['form'] = $form->createView();

        return $this->render('security/change-password.html.twig', $responseParams);
    }

    /**
     * @throws Exception
     */
    #[Route("/logout", name: "logout")]
    public function logout(): void
    {
        throw new RuntimeException('Access denied');
    }
}
