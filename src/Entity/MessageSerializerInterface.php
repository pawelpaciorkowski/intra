<?php

declare(strict_types=1);

namespace App\Entity;

interface MessageSerializerInterface
{
    public static function getSerializedGroups(): array;
}
