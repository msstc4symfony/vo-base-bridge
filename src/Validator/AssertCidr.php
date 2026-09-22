<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObjectSymfonyBridge\Validator;

use Attribute;
use MSSTC4PHP\ValueObject\Network\Cidr;

/**
 * Asserts that a string is a valid {@see Cidr} block.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class AssertCidr extends ValidValueObject
{
    /**
     * @param array<string>|null $groups
     */
    public function __construct(?array $groups = null, mixed $payload = null)
    {
        parent::__construct(Cidr::class, 'The value {{ value }} is not a valid CIDR block.', $groups, $payload);
    }
}
