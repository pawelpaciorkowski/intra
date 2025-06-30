<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Validator\Constraints\Helper\PeselValidator as PeselValidatorHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PeselValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        /** @noinspection PhpConditionCheckedByNextConditionInspection */
        if (null === $value || !$value) {
            return;
        }

        if (!PeselValidatorHelper::validatePesel($value)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
