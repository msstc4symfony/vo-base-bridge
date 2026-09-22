<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObjectSymfonyBridge\Validator;

use Attribute;
use MSSTC4PHP\ValueObject\Format\Slug;

/**
 * Asserts that a string is a valid {@see Slug}.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class AssertSlug extends ValidValueObject
{
    /**
     * @param array<string>|null $groups
     */
    public function __construct(?array $groups = null, mixed $payload = null)
    {
        parent::__construct(Slug::class, 'The value {{ value }} is not a valid slug.', $groups, $payload);
    }
}
