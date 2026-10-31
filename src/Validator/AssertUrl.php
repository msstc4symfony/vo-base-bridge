<?php

declare(strict_types=1);

namespace Msstc4Symfony\VoBaseBridge\Validator;

use Attribute;
use MSSTC4PHP\ValueObject\Network\Url;

/**
 * Asserts that a string is a valid {@see Url}.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class AssertUrl extends ValidValueObject
{
    /**
     * @param array<string>|null $groups
     */
    public function __construct(?array $groups = null, mixed $payload = null)
    {
        parent::__construct(Url::class, 'The value {{ value }} is not a valid URL.', $groups, $payload);
    }
}
