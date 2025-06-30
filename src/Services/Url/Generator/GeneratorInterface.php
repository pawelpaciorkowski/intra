<?php

declare(strict_types=1);

namespace App\Services\Url\Generator;

interface GeneratorInterface
{
    public function generate(object $object): string;
    public function isSupport(object $object): bool;
}
