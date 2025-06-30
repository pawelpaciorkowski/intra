<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Psajdak;
use App\Form\Type\PsajdakType;
use App\Services\PsajdakService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/psajdak")] final class PsajdakController extends AbstractController
{
    #[Route("/request", name: "psajdak-request")]
    public function request(Request $request, PsajdakService $psajdakService): Response
    {
        $form = $this->createForm(PsajdakType::class, new Psajdak());

        $form->handleRequest($request);
        $status = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $status = true;

            $psajdakService->publish([
                'sender_info' => 'app.name:intranet',
                'type' => $form->get('type')->getData(),
                'from_name' => $form->get('fromName')->getData(),
                'from_address' => $form->get('fromEmail')->getData(),
                'to_addresses' => $form->get('email')->getData() ? preg_split(
                    '/(,\s?)/',
                    $form->get('email')->getData()
                ) : null,
                'subject' => $form->get('subject')->getData(),
                'text_content' => $form->get('textBody')->getData(),
                'html_content' => $form->get('htmlBody')->getData(),
            ]);

            $form = $this->createForm(PsajdakType::class, new Psajdak());
        }

        return $this->render(
            'psajdak/form.html.twig',
            [
                'form' => $form->createView(),
                'status' => $status,
            ]
        );
    }
}
