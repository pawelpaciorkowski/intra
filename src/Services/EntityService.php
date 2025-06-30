<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use App\Services\Component\ParameterBagInterface;

final class EntityService
{
    private $entityManager;
    private $tagAwareAdapter;

    public function __construct(EntityManagerInterface $entityManager, TagAwareAdapter $tagAwareAdapter)
    {
        $this->entityManager = $entityManager;
        $this->tagAwareAdapter = $tagAwareAdapter;
    }

    public function findAllByParams(string $className, ParameterBagInterface $parameterBag)
    {
        if (!$parameterBag->has('cached') || !$parameterBag->get('cached')) {
            return $this->invokeRepository($className, $parameterBag);
        }

        $tagGenerator = new Tag($className);
        $tag = $tagGenerator->generateTag($parameterBag);

        $item = $this->tagAwareAdapter->getItem($tag);

        if (!$item->isHit()) {
            $item->set($this->invokeRepository($className, $parameterBag));
            $tag = $tagGenerator->getStrippedClassname();
            if ($parameterBag->has('cacheTag') && $parameterBag->get('cacheTag')) {
                $tag = $parameterBag->get('cacheTag');
            }
            $item->tag($tag);
            $this->tagAwareAdapter->save($item);
        }

        return $item->get();
    }

    private function invokeRepository(string $className, ParameterBagInterface $parameterBag)
    {
        return $this->entityManager->getRepository($className)->findAllByParams($parameterBag);
    }
}
