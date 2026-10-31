<?php

declare(strict_types=1);

namespace Msstc4Symfony\VoBaseBridge\Validator;

use Attribute;
use MSSTC4PHP\ValueObject\Temporal\Duration;

/**
 * Asserts that a string is a valid ISO 8601 {@see Duration}.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class AssertDuration extends ValidValueObject
{
    /**
     * @param array<string>|null $groups
     */
    public function __construct(?array $groups = null, mixed $payload = null)
    {
        parent::__construct(Duration::class, 'The value {{ value }} is not a valid ISO 8601 duration.', $groups, $payload);
    }
}
