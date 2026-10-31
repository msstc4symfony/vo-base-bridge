<?php

declare(strict_types=1);

namespace Msstc4Symfony\VoBaseBridge\Test\Validator;

use MSSTC4PHP\ValueObject\Identity\EmailAddress;
use Msstc4Symfony\VoBaseBridge\Validator\ValidValueObject;
use Msstc4Symfony\VoBaseBridge\Validator\ValidValueObjectValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(ValidValueObjectValidator::class)]
#[CoversClass(ValidValueObject::class)]
final class ValidValueObjectValidatorTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidator();
    }

    public function testValidInputYieldsNoViolations(): void
    {
        $violations = $this->validator->validate('user@example.com', new ValidValueObject(EmailAddress::class));

        self::assertCount(0, $violations);
    }

    public function testInvalidInputYieldsSingleViolationWithValueParameter(): void
    {
        $violations = $this->validator->validate('not-an-email', new ValidValueObject(EmailAddress::class));

        self::assertCount(1, $violations);
        self::assertArrayHasKey('{{ value }}', $violations->get(0)->getParameters());
        self::assertSame('not-an-email', $violations->get(0)->getParameters()['{{ value }}']);
    }

    public function testNullYieldsNoViolations(): void
    {
        $violations = $this->validator->validate(null, new ValidValueObject(EmailAddress::class));

        self::assertCount(0, $violations);
    }

    public function testEmptyStringYieldsNoViolations(): void
    {
        $violations = $this->validator->validate('', new ValidValueObject(EmailAddress::class));

        self::assertCount(0, $violations);
    }

    public function testNonStringValueYieldsTypeViolation(): void
    {
        // The engine converts our UnexpectedValueException into a type violation.
        $violations = $this->validator->validate(123, new ValidValueObject(EmailAddress::class));

        self::assertCount(1, $violations);
    }

    /**
     * The constraint must report a violation IF AND ONLY IF the value object
     * rejects the input — proving they never diverge.
     */
    #[DataProvider('parityInputs')]
    public function testParityWithValueObject(string $input): void
    {
        $isValid = EmailAddress::isValid($input);
        $violations = $this->validator->validate($input, new ValidValueObject(EmailAddress::class));

        self::assertSame($isValid, $violations->count() === 0);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function parityInputs(): iterable
    {
        yield 'plain valid' => ['user@example.com'];
        yield 'subdomain valid' => ['a.b@mail.example.co.uk'];
        yield 'plus tag valid' => ['user+tag@example.com'];
        yield 'missing at' => ['userexample.com'];
        yield 'missing domain' => ['user@'];
        yield 'spaces' => ['user @example.com'];
        yield 'double at' => ['user@@example.com'];
        yield 'trailing dot only' => ['user@.'];
    }
}
