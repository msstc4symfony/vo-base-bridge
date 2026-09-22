<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObjectSymfonyBridge\Validator;

use Attribute;
use MSSTC4PHP\ValueObject\Identity\Uuid;

/**
 * Asserts that a string is a valid {@see Uuid}.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class AssertUuid extends ValidValueObject
{
    /**
     * @param array<string>|null $groups
     */
    public function __construct(?array $groups = null, mixed $payload = null)
    {
        parent::__construct(Uuid::class, 'The value {{ value }} is not a valid UUID.', $groups, $payload);
    }
}
