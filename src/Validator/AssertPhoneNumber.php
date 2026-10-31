<?php

declare(strict_types=1);

namespace Msstc4Symfony\VoBaseBridge\Validator;

use Attribute;
use MSSTC4PHP\ValueObject\Format\PhoneNumber;

/**
 * Asserts that a string is a valid {@see PhoneNumber}.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class AssertPhoneNumber extends ValidValueObject
{
    /**
     * @param array<string>|null $groups
     */
    public function __construct(?array $groups = null, mixed $payload = null)
    {
        parent::__construct(PhoneNumber::class, 'The value {{ value }} is not a valid phone number.', $groups, $payload);
    }
}
