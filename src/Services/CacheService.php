<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Entity\Tag;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use App\Services\Component\ParameterBagInterface;

final class CacheService
{
    private $tagAwareAdapter;

    public function __construct(TagAwareAdapter $tagAwareAdapter)
    {
        $this->tagAwareAdapter = $tagAwareAdapter;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function removeFromCache(string $className, ParameterBagInterface $parameterBag): void
    {
        $tagGenerator = new Tag($className);
        $tag = $tagGenerator->generateTag($parameterBag);

        $this->tagAwareAdapter->deleteItem($tag);
    }

    public function removeFromCacheByTag(string $cacheTag): bool
    {
        return $this->removeFromCacheByTags([$cacheTag]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function removeFromCacheByTags(array $cacheTags): bool
    {
        return $this->tagAwareAdapter->invalidateTags($cacheTags);
    }
}
