<?php

declare(strict_types=1);

namespace App\Services\Url\Generator;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class Category implements GeneratorInterface
{
    public function __construct(private UrlGeneratorInterface $generator)
    {
    }

    public function generate(object $object): string
    {
        if ($object->getLink()) {
            return $this->generator->generate($object->getLink()->getName());
        }

        if ($object->getPage()) {
            return $this->generator->generate('page-view', ['id' => $object->getPage()->getId()]);
        }

        if ($object->getUrl()) {
            return $object->getUrl();
        }

        return $this->generator->generate('category-view', ['id' => $object->getId()]);
    }

    public function isSupport(object $object): bool
    {
        return $object instanceof \App\Entity\Category;
    }
}
