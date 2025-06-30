<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class RouteExists extends Constraint
{
    public string $message = 'Podany link nie istnieje.';

    public function validatedBy(): string
    {
        return RouteExistsValidator::class;
    }
}
