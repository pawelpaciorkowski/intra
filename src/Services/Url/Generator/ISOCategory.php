<?php

declare(strict_types=1);

namespace App\Services\Url\Generator;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ISOCategory implements GeneratorInterface
{
    public function __construct(private UrlGeneratorInterface $generator)
    {
    }

    public function generate(object $object): string
    {
        return $this->generator->generate('iso');
    }

    public function isSupport(object $object): bool
    {
        return $object instanceof \App\Entity\ISOCategory;
    }
}
