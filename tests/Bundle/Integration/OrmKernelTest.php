<?php

declare(strict_types=1);

namespace Msstc4Symfony\VoBaseBridge\Test\Bundle\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use MSSTC4PHP\ValueObject\Identity\EmailAddress;
use MSSTC4PHP\ValueObjectDoctrineBridge\Orm\EmailAddressType;
use Msstc4Symfony\VoBaseBridge\Test\Bundle\Integration\Kernel\IntegrationKernel;
use Msstc4Symfony\VoBaseBridge\Validator\AssertEmailAddress;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Proves the bundle in a real container, not against stubs: prepending the type
 * map is one thing, DoctrineBundle actually accepting it is another.
 */
#[CoversNothing]
final class OrmKernelTest extends TestCase
{
    // DBAL only: type registration goes through doctrine.dbal.types, and
    // enabling the ORM section would pull in the proxy factory, which is beside
    // the point here.
    private const array DBAL_CONFIG = [
        'dbal' => ['driver' => 'pdo_sqlite', 'url' => 'sqlite:///:memory:'],
    ];

    public function testKernelBootsAndDoctrineKnowsTheTypes(): void
    {
        $kernel = new IntegrationKernel([new DoctrineBundle()], ['doctrine' => self::DBAL_CONFIG], 'orm');
        $kernel->boot();

        // DoctrineBundle hands the prepended map to the connection factory, so
        // the DBAL registry fills when a connection is built, not at boot.
        $this->connection($kernel);

        self::assertTrue(Type::hasType(EmailAddressType::NAME));
        self::assertInstanceOf(EmailAddressType::class, Type::getType(EmailAddressType::NAME));

        $kernel->shutdown();
    }

    public function testConstraintValidatorIsWiredEndToEnd(): void
    {
        $kernel = new IntegrationKernel([new DoctrineBundle()], ['doctrine' => self::DBAL_CONFIG], 'orm-validator');
        $kernel->boot();

        $validator = $this->testContainer($kernel)->get(ValidatorInterface::class);
        self::assertInstanceOf(ValidatorInterface::class, $validator);

        self::assertCount(0, $validator->validate('user@example.com', new AssertEmailAddress()));
        self::assertGreaterThan(0, \count($validator->validate('not-an-email', new AssertEmailAddress())));

        $kernel->shutdown();
    }

    private function testContainer(IntegrationKernel $kernel): ContainerInterface
    {
        $container = $kernel->getContainer()->get('test.service_container');
        self::assertInstanceOf(ContainerInterface::class, $container);

        return $container;
    }

    private function connection(IntegrationKernel $kernel): Connection
    {
        $connection = $this->testContainer($kernel)->get('doctrine.dbal.default_connection');
        self::assertInstanceOf(Connection::class, $connection);

        return $connection;
    }

    public function testExplicitFalseKeepsDoctrineWithoutTheTypes(): void
    {
        // A fresh type name that no other test registers, so the assertion is
        // about this kernel rather than about global registry leftovers.
        $kernel = new IntegrationKernel(
            [new DoctrineBundle()],
            ['doctrine' => self::DBAL_CONFIG, 'value_object_bridge' => ['orm' => false]],
            'orm-disabled',
        );
        $kernel->boot();

        $registered = $kernel->getContainer()->getParameter('doctrine.dbal.connection_factory.types');
        self::assertIsArray($registered);
        self::assertArrayNotHasKey(EmailAddressType::NAME, $registered);

        $kernel->shutdown();
    }

    public function testValueObjectSurvivesTheRegisteredType(): void
    {
        $kernel = new IntegrationKernel([new DoctrineBundle()], ['doctrine' => self::DBAL_CONFIG], 'orm-roundtrip');
        $kernel->boot();

        $platform = $this->connection($kernel)->getDatabasePlatform();
        $type = Type::getType(EmailAddressType::NAME);

        $email = EmailAddress::from('user@example.com');
        $back = $type->convertToPHPValue($type->convertToDatabaseValue($email, $platform), $platform);

        self::assertInstanceOf(EmailAddress::class, $back);
        self::assertTrue($email->equals($back));

        $kernel->shutdown();
    }
}
