<?php

declare(strict_types=1);

namespace Msstc4Symfony\VoBaseBridge\Validator;

use Attribute;
use MSSTC4PHP\ValueObject\Identity\EmailAddress;

/**
 * Asserts that a string is a valid {@see EmailAddress}.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class AssertEmailAddress extends ValidValueObject
{
    /**
     * @param array<string>|null $groups
     */
    public function __construct(?array $groups = null, mixed $payload = null)
    {
        parent::__construct(EmailAddress::class, 'The value {{ value }} is not a valid e-mail address.', $groups, $payload);
    }
}
