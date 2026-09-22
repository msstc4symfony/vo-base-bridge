<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObjectSymfonyBridge\Tests\Validator;

use MSSTC4PHP\ValueObjectSymfonyBridge\Validator\AssertEmailAddress;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(AssertEmailAddress::class)]
final class AssertEmailAddressTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidator();
    }

    public function testAcceptsValidEmail(): void
    {
        self::assertCount(0, $this->validator->validate('user@example.com', new AssertEmailAddress()));
    }

    public function testRejectsInvalidEmail(): void
    {
        $violations = $this->validator->validate('nope', new AssertEmailAddress());

        self::assertCount(1, $violations);
        self::assertSame('The value nope is not a valid e-mail address.', $violations->get(0)->getMessage());
    }
}
