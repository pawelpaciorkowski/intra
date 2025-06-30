<?php

declare(strict_types=1);

namespace App\Services\Entity;

use App\Services\Component\ParameterBagInterface;

use function md5;
use function serialize;
use function strrpos;
use function substr;

final class Tag
{
    private $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function generateTag(ParameterBagInterface $parameterBag): string
    {
        if ($parameterBag->has('cacheId') && $parameterBag->get('cacheId')) {
            return $this->getStrippedClassname() . '_' . md5(serialize($parameterBag->get('cacheId')));
        }

        return $this->getStrippedClassname() . '_' . md5(serialize($parameterBag));
    }

    public function getStrippedClassname(): string
    {
        return substr($this->className, strrpos($this->className, '\\') + 1);
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
    }
}
