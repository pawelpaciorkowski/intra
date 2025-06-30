<?php

declare(strict_types=1);

namespace App\Services;

final class TagService
{
    public function getTags(array $collection): array
    {
        $tags = [];

        foreach ($collection as $category) {
            foreach ($category->getFiles() as $file) {
                if (!$file->getIsActive()) {
                    continue;
                }

                foreach ($file->getTags() as $tag) {
                    if (array_key_exists($tag->getId(), $tags)) {
                        $tags[$tag->getId()]['count']++;
                    } else {
                        $tags[$tag->getId()] = [
                            'id' => $tag->getId(),
                            'name' => $tag->getName(),
                            'count' => 1,
                        ];
                    }
                }
            }
        }

        usort($tags, static function ($a, $b) {
            return $a['name'] <=> $b['name'];
        });

        return $tags;
    }
}
