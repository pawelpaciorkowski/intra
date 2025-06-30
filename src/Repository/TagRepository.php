<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function getTags(?string $tags): array
    {
        if (!$tags) {
            return [];
        }

        $tagNames = json_decode($tags, true, 512, JSON_THROW_ON_ERROR);

        $tagEntities = [];
        foreach ($tagNames as $tagName) {
            $rawName = trim($tagName['value']);

            $tag = $this->findOneBy(['name' => $rawName]);

            if (!$tag) {
                $tag = new Tag();
                $tag->setName($rawName);
                $this->getEntityManager()->persist($tag);
            }

            $tagEntities[] = $tag;
        }

        return $tagEntities;
    }
}
