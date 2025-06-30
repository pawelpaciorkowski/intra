<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use function array_keys;
use function in_array;

final class RouteExistsValidator extends ConstraintValidator
{
    public function __construct(protected RouterInterface $router)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        // can be empty
        /** @noinspection PhpConditionCheckedByNextConditionInspection */
        if (null === $value || !$value) {
            return;
        }

        $routes = array_keys($this->router->getRouteCollection()->all());

        if (!in_array($value, $routes, true)) {
            $this->context->buildViolation($constraint->message)->setParameter('%string%', $value)->addViolation();
        }
    }
}
