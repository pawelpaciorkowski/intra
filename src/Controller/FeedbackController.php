<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Entity\Feedback;
use App\Event\EmailEvent;
use App\Form\Type\FeedbackType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/feedback")] final class FeedbackController extends AbstractController
{
    #[Route("/request", name: "feedback-request")]
    public function request(Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        $feedback = new Feedback();

        $form = $this->createForm(FeedbackType::class, $feedback);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();

            $emailEvent = new EmailEvent(
                'feedback',
                [
                    'user' => $user,
                    'subject' => $feedback->getSubject(),
                    'body' => $feedback->getBody(),
                ]
            );
            $eventDispatcher->dispatch($emailEvent);

            return $this->redirectToRoute('feedback-requested');
        }

        return $this->render(
            'feedback/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route("/requested", name: "feedback-requested")]
    #[Breadcrumb("Kontakt")]
    public function requested(): Response
    {
        return $this->render('feedback/requested.html.twig');
    }
}
