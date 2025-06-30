<?php

declare(strict_types=1);

namespace App\Twig;

use DateTimeInterface;
use JsonException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

use function is_a;
use function is_array;
use function is_object;
use function json_decode;
use function json_encode;
use function serialize;

use const JSON_PRETTY_PRINT;

final class DateExtension
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function beautify(Environment $env, $data): ?string
    {
        if (!is_array($data)) {
            return $data;
        }

        $return = [];

        foreach ($data as $key => $value) {
            if (is_object($value) && is_a($value, DateTimeInterface::class)) {
                $value = $value->format('d.m.Y H:i:s');
            } elseif (is_array($value) || is_object($value)) {
                $value = serialize($value);
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            $return[$key] = $value;
        }

        return $env->render('twig/beautify.html.twig', ['data' => $return]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws JsonException
     */
    public function jsonBeautify(Environment $env, string $data): string
    {
        if (!$data) {
            return '';
        }

        $json = json_encode(json_decode($data, false, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        return $env->render('twig/json-beautify.html.twig', ['json' => $json]);
    }
}
