<?php

declare(strict_types=1);

namespace Msstc4Symfony\VoBaseBridge\Validator;

use Attribute;
use MSSTC4PHP\ValueObject\Finance\Iban;

/**
 * Asserts that a string is a valid {@see Iban}.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class AssertIban extends ValidValueObject
{
    /**
     * @param array<string>|null $groups
     */
    public function __construct(?array $groups = null, mixed $payload = null)
    {
        parent::__construct(Iban::class, 'The value {{ value }} is not a valid IBAN.', $groups, $payload);
    }
}
