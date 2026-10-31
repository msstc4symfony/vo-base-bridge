<?php

declare(strict_types=1);

namespace Msstc4Symfony\VoBaseBridge\Validator;

use Attribute;
use MSSTC4PHP\ValueObject\Network\Ipv4Address;

/**
 * Asserts that a string is a valid {@see Ipv4Address}.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class AssertIpv4 extends ValidValueObject
{
    /**
     * @param array<string>|null $groups
     */
    public function __construct(?array $groups = null, mixed $payload = null)
    {
        parent::__construct(Ipv4Address::class, 'The value {{ value }} is not a valid IPv4 address.', $groups, $payload);
    }
}
