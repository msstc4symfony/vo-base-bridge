<?php

declare(strict_types=1);

namespace Msstc4Symfony\VoBaseBridge\Validator;

use Attribute;
use MSSTC4PHP\ValueObject\Contract\StringConvertible;
use Override;
use Symfony\Component\Validator\Constraint;

/**
 * Generic constraint asserting that a string can construct the given
 * value-object class. Validation is delegated entirely to the value object, so
 * the constraint never re-implements a rule.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class ValidValueObject extends Constraint
{
    public string $message = 'The value {{ value }} is not a valid {{ type }}.';

    /**
     * @param class-string<StringConvertible> $class
     * @param array<string>|null $groups
     */
    public function __construct(public string $class, ?string $message = null, ?array $groups = null, mixed $payload = null)
    {
        if ($message !== null) {
            $this->message = $message;
        }

        parent::__construct(null, $groups, $payload);
    }

    #[Override]
    public function validatedBy(): string
    {
        return ValidValueObjectValidator::class;
    }
}
