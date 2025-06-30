<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

trait RelatedObject
{
    #[ORM\Column(nullable: true)]
    #[Groups(["phone", "calendar", "period", "user"])]
    private ?string $cachedRelatedObjectName = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["phone", "calendar", "period", "user"])]
    private ?string $cachedRelatedObjectUuid = null;

    public function getCachedRelatedObjectName(): ?string
    {
        return $this->cachedRelatedObjectName;
    }

    public function setCachedRelatedObjectName(?string $cachedRelatedObjectName): self
    {
        $this->cachedRelatedObjectName = $cachedRelatedObjectName;

        return $this;
    }

    public function getCachedRelatedObjectUuid(): ?string
    {
        return $this->cachedRelatedObjectUuid;
    }

    public function setCachedRelatedObjectUuid(?string $cachedRelatedObjectUuid): self
    {
        $this->cachedRelatedObjectUuid = $cachedRelatedObjectUuid;

        return $this;
    }

    private function setRelatedObject(?object $object): void
    {
        if (is_object($object)) {
            $className = $object::class;

            $this->cachedRelatedObjectName = lcfirst(substr($className, strrpos($className, '\\') + 1));
            $this->cachedRelatedObjectUuid = $object->getUuid();
        }
    }
}
