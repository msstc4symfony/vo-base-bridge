<?php

declare(strict_types=1);

namespace Msstc4Symfony\VoBaseBridge\Test\Validator;

use Msstc4Symfony\VoBaseBridge\Validator\AssertBic;
use Msstc4Symfony\VoBaseBridge\Validator\AssertCidr;
use Msstc4Symfony\VoBaseBridge\Validator\AssertCreditCardNumber;
use Msstc4Symfony\VoBaseBridge\Validator\AssertDecimal;
use Msstc4Symfony\VoBaseBridge\Validator\AssertDuration;
use Msstc4Symfony\VoBaseBridge\Validator\AssertEmailAddress;
use Msstc4Symfony\VoBaseBridge\Validator\AssertIban;
use Msstc4Symfony\VoBaseBridge\Validator\AssertIpv4;
use Msstc4Symfony\VoBaseBridge\Validator\AssertPhoneNumber;
use Msstc4Symfony\VoBaseBridge\Validator\AssertSlug;
use Msstc4Symfony\VoBaseBridge\Validator\AssertTimeOfDay;
use Msstc4Symfony\VoBaseBridge\Validator\AssertUrl;
use Msstc4Symfony\VoBaseBridge\Validator\AssertUuid;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(AssertBic::class)]
#[CoversClass(AssertCidr::class)]
#[CoversClass(AssertCreditCardNumber::class)]
#[CoversClass(AssertDecimal::class)]
#[CoversClass(AssertDuration::class)]
#[CoversClass(AssertEmailAddress::class)]
#[CoversClass(AssertIban::class)]
#[CoversClass(AssertIpv4::class)]
#[CoversClass(AssertPhoneNumber::class)]
#[CoversClass(AssertSlug::class)]
#[CoversClass(AssertTimeOfDay::class)]
#[CoversClass(AssertUrl::class)]
#[CoversClass(AssertUuid::class)]
final class AssertConstraintsTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidator();
    }

    /**
     * @return iterable<string, array{class-string<Constraint>, string, string}>
     */
    public static function constraints(): iterable
    {
        yield 'email' => [AssertEmailAddress::class, 'user@example.com', 'nope'];
        yield 'ipv4' => [AssertIpv4::class, '192.168.0.1', '999.1.1.1'];
        yield 'phone' => [AssertPhoneNumber::class, '+14155552671', 'abc'];
        yield 'slug' => [AssertSlug::class, 'my-slug', 'Not a Slug!'];
        yield 'url' => [AssertUrl::class, 'https://example.com/a', 'not a url'];
        yield 'uuid' => [AssertUuid::class, '018f8c1e-7b2a-7cde-8f01-2a3b4c5d6e7f', 'not-a-uuid'];
        yield 'iban' => [AssertIban::class, 'GB82WEST12345698765432', 'not-an-iban'];
        yield 'bic' => [AssertBic::class, 'DEUTDEFF', 'nope'];
        yield 'credit_card_number' => [AssertCreditCardNumber::class, '4111111111111111', '4111111111111112'];
        yield 'decimal' => [AssertDecimal::class, '1.23', 'not-a-number'];
        yield 'cidr' => [AssertCidr::class, '192.168.0.0/24', 'not-a-cidr'];
        yield 'time_of_day' => [AssertTimeOfDay::class, '09:30:00', '25:00:00'];
        yield 'duration' => [AssertDuration::class, 'PT30S', 'not-a-duration'];
    }

    /**
     * @param class-string<Constraint> $constraintClass
     */
    #[DataProvider('constraints')]
    public function testValidValueHasNoViolations(string $constraintClass, string $valid, string $invalid): void
    {
        self::assertCount(0, $this->validator->validate($valid, new $constraintClass()));
    }

    /**
     * @param class-string<Constraint> $constraintClass
     */
    #[DataProvider('constraints')]
    public function testInvalidValueHasOneViolation(string $constraintClass, string $valid, string $invalid): void
    {
        self::assertCount(1, $this->validator->validate($invalid, new $constraintClass()));
    }
}
