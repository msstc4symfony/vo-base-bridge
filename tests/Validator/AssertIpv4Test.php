<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObjectSymfonyBridge\Tests\Validator;

use MSSTC4PHP\ValueObjectSymfonyBridge\Validator\AssertIpv4;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(AssertIpv4::class)]
final class AssertIpv4Test extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidator();
    }

    public function testAcceptsValidIpv4(): void
    {
        self::assertCount(0, $this->validator->validate('192.168.0.1', new AssertIpv4()));
    }

    public function testRejectsInvalidIpv4(): void
    {
        self::assertCount(1, $this->validator->validate('999.1.1.1', new AssertIpv4()));
    }
}
