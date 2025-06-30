<?php

declare(strict_types=1);

namespace App\Services\Url;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final class UrlService
{
    public function __construct(#[TaggedIterator('app.url_generator')] private iterable $urlGenerators)
    {
    }

    public function generate(object $object): ?string
    {
        foreach ($this->urlGenerators as $urlGenerator) {
            if ($urlGenerator->isSupport($object)) {
                return $urlGenerator->generate($object);
            }
        }

        return null;
    }
}
