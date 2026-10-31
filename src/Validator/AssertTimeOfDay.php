<?php

declare(strict_types=1);

namespace Msstc4Symfony\VoBaseBridge\Validator;

use Attribute;
use MSSTC4PHP\ValueObject\Temporal\TimeOfDay;

/**
 * Asserts that a string is a valid {@see TimeOfDay}.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class AssertTimeOfDay extends ValidValueObject
{
    /**
     * @param array<string>|null $groups
     */
    public function __construct(?array $groups = null, mixed $payload = null)
    {
        parent::__construct(TimeOfDay::class, 'The value {{ value }} is not a valid time of day.', $groups, $payload);
    }
}
