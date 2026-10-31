<?php

declare(strict_types=1);

namespace Msstc4Symfony\VoBaseBridge\Validator;

use MSSTC4PHP\ValueObject\Exception\InvalidValueObjectException;
use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Delegates validation to the value object referenced by the constraint: the
 * value object is the single source of validation truth.
 */
final class ValidValueObjectValidator extends ConstraintValidator
{
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidValueObject) {
            throw new UnexpectedTypeException($constraint, ValidValueObject::class);
        }

        // Emptiness is the concern of NotBlank/NotNull, not of this constraint.
        if ($value === null || $value === '') {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        try {
            ($constraint->class)::from($value);
        } catch (InvalidValueObjectException) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->setParameter('{{ type }}', $constraint->class)
                ->addViolation()
            ;
        }
    }
}
