<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final class TextExtension
{
    // Maximum numer of characters to be display
    public const int MAX = 100;

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function expand(Environment $env, ?string $text): ?string
    {
        if (!$text) {
            return null;
        }

        return $env->render('twig/expand.html.twig', [
            'text' => $text,
            'max' => self::MAX,
        ]);
    }

    public function camel2dashed(Environment $env, ?string $text): ?string
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $text));
    }
}
