<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class ExceptionController extends AbstractController
{
    public function showException(Request $request, FlattenException $exception, Environment $twig): Response
    {
        $format = $request->getRequestFormat();

        $template = 'exception/error' . $exception->getStatusCode() . '.' . $format . '.twig';
        if (!$twig->getLoader()->exists($template)) {
            $template = 'exception/error.' . $format . '.twig';

            if (!$twig->getLoader()->exists($template)) {
                $template = 'exception/error.html.twig';
            }
        }

        return $this->render(
            $template,
            [
                'message' => $exception->getMessage(),
                'code' => $exception->getStatusCode(),
            ]
        );
    }
}
