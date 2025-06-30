<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final class SimpleColumnExtension
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function simpleColumnOrder(
        Environment $env,
        string $title,
        int $columnNumber,
        ?int $currentOrder = 0
    ): string {
        if (!$currentOrder) {
            $currentOrder = 1;
        }

        return $env->render(
            'twig/simple-column-order.html.twig',
            [
                'title' => $title,
                'currentOrder' => $currentOrder,
                'columnNumber' => $columnNumber,
            ]
        );
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function jsColumnOrder(Environment $env, string $title, int $columnNumber, ?int $currentOrder = 0): string
    {
        if (!$currentOrder) {
            $currentOrder = 1;
        }

        return $env->render(
            'twig/js-column-order.html.twig',
            [
                'title' => $title,
                'currentOrder' => $currentOrder,
                'columnNumber' => $columnNumber,
            ]
        );
    }
}
