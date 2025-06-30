<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Pesel extends Constraint
{
    public string $message = 'Ta wartość jest nieprawidłowym numerem PESEL';

    public function validatedBy(): string
    {
        return PeselValidator::class;
    }
}
