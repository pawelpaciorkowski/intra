<?php

declare(strict_types=1);

namespace App\Services\Url\Generator;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Employee implements GeneratorInterface
{
    public function __construct(private UrlGeneratorInterface $generator)
    {
    }

    public function generate(object $object): string
    {
        return $this->generator->generate('employee-search-public');
    }

    public function isSupport(object $object): bool
    {
        return $object instanceof \App\Entity\Employee;
    }
}
