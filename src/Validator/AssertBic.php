<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObjectSymfonyBridge\Validator;

use Attribute;
use MSSTC4PHP\ValueObject\Finance\Bic;

/**
 * Asserts that a string is a valid {@see Bic}.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class AssertBic extends ValidValueObject
{
    /**
     * @param array<string>|null $groups
     */
    public function __construct(?array $groups = null, mixed $payload = null)
    {
        parent::__construct(Bic::class, 'The value {{ value }} is not a valid BIC.', $groups, $payload);
    }
}
