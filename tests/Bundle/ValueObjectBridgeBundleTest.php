<?php

declare(strict_types=1);

namespace Msstc4Symfony\VoBaseBridge\Test\Bundle;

use Doctrine\DBAL\Types\Type;
use LogicException;
use MSSTC4PHP\ValueObjectDoctrineBridge\Odm\TypeRegistrar as OdmRegistrar;
use MSSTC4PHP\ValueObjectDoctrineBridge\Orm\TypeRegistrar as OrmRegistrar;
use Msstc4Symfony\VoBaseBridge\Bundle\ValueObjectBridgeBundle;
use Msstc4Symfony\VoBaseBridge\Validator\ValidValueObjectValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

#[CoversClass(ValueObjectBridgeBundle::class)]
final class ValueObjectBridgeBundleTest extends TestCase
{
    /**
     * The positive paths need the Doctrine bridge and the Doctrine stack behind
     * it. Skipping rather than failing keeps this suite meaningful in the CI job
     * that removes both to prove the bundle works without them -- the negative
     * paths still run there, which is exactly what that job is checking.
     */
    private function requireDoctrineBridge(): void
    {
        if (!\class_exists(OrmRegistrar::class) || !\class_exists(Type::class)) {
            self::markTestSkipped('The Doctrine bridge package and its Doctrine stack are not installed.');
        }
    }

    public function testExtensionAliasIsDerivedFromTheBundleName(): void
    {
        $extension = new ValueObjectBridgeBundle()->getContainerExtension();

        self::assertNotNull($extension);
        self::assertSame('value_object_bridge', $extension->getAlias());
    }

    public function testOrmTypesArePrependedWhenBothPackageAndExtensionArePresent(): void
    {
        $this->requireDoctrineBridge();

        $container = $this->containerWith(['doctrine']);

        new ValueObjectBridgeBundle()->prependExtension($this->configurator($container), $container);

        $prepended = $container->getExtensionConfig('doctrine');
        self::assertSame([['dbal' => ['types' => OrmRegistrar::types()]]], $prepended);
    }

    public function testOdmTypesArePrependedIntoTheirOwnExtension(): void
    {
        $this->requireDoctrineBridge();

        $container = $this->containerWith(['doctrine_mongodb']);

        new ValueObjectBridgeBundle()->prependExtension($this->configurator($container), $container);

        self::assertSame([['types' => OdmRegistrar::types()]], $container->getExtensionConfig('doctrine_mongodb'));
    }

    /**
     * Type names are identical on both sides, so the two maps can only be told
     * apart by their class values.
     */
    public function testBothExtensionsGetTheirOwnBranch(): void
    {
        $this->requireDoctrineBridge();

        $container = $this->containerWith(['doctrine', 'doctrine_mongodb']);

        new ValueObjectBridgeBundle()->prependExtension($this->configurator($container), $container);

        $orm = $this->typeMap($container->getExtensionConfig('doctrine')[0] ?? null, 'dbal');
        $odm = $this->typeMap($container->getExtensionConfig('doctrine_mongodb')[0] ?? null, null);

        self::assertSame(\array_keys($orm), \array_keys($odm));

        foreach ($orm as $class) {
            self::assertStringContainsString('\\Orm\\', $class);
        }

        foreach ($odm as $class) {
            self::assertStringContainsString('\\Odm\\', $class);
        }
    }

    public function testNothingHappensWhenTheDoctrineExtensionIsAbsent(): void
    {
        $this->requireDoctrineBridge();

        $container = $this->containerWith([]);

        new ValueObjectBridgeBundle()->prependExtension($this->configurator($container), $container);

        self::assertSame([], $container->getExtensionConfig('doctrine'));
    }

    /**
     * The scenario KTD3 exists for: the Doctrine extension is there, but the
     * bridge package is not installed. Silence, not an error.
     */
    public function testNothingHappensWhenTheDoctrinePackageIsAbsent(): void
    {
        $container = $this->containerWith(['doctrine', 'doctrine_mongodb']);

        new BundleWithoutDoctrinePackage()->prependExtension($this->configurator($container), $container);

        self::assertSame([], $container->getExtensionConfig('doctrine'));
        self::assertSame([], $container->getExtensionConfig('doctrine_mongodb'));
    }

    public function testExplicitFalseSuppressesRegistration(): void
    {
        $this->requireDoctrineBridge();

        $container = $this->containerWith(['doctrine']);
        $container->prependExtensionConfig('value_object_bridge', ['orm' => false]);

        new ValueObjectBridgeBundle()->prependExtension($this->configurator($container), $container);

        self::assertSame([], $container->getExtensionConfig('doctrine'));
    }

    public function testExplicitTrueFailsLoudlyWhenThePackageIsMissing(): void
    {
        $container = $this->containerWith(['doctrine']);
        $container->prependExtensionConfig('value_object_bridge', ['orm' => true]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/vo-base-doctrine-bridge or the Doctrine stack it needs is not installed/');

        new BundleWithoutDoctrinePackage()->prependExtension($this->configurator($container), $container);
    }

    public function testExplicitTrueFailsLoudlyWhenTheExtensionIsMissing(): void
    {
        $this->requireDoctrineBridge();

        $container = $this->containerWith([]);
        $container->prependExtensionConfig('value_object_bridge', ['orm' => true]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/"doctrine" extension is not registered/');

        new ValueObjectBridgeBundle()->prependExtension($this->configurator($container), $container);
    }

    public function testPrependingTwiceDoesNotDuplicateTypes(): void
    {
        $this->requireDoctrineBridge();

        $container = $this->containerWith(['doctrine']);
        $bundle = new ValueObjectBridgeBundle();

        $bundle->prependExtension($this->configurator($container), $container);
        $bundle->prependExtension($this->configurator($container), $container);

        $configs = $container->getExtensionConfig('doctrine');
        self::assertCount(2, $configs);
        self::assertSame($configs[0], $configs[1], 'Repeated passes must stay identical, not accumulate different maps.');
    }

    public function testValidatorServiceIsRegisteredAndTagged(): void
    {
        $container = new ContainerBuilder();

        new ValueObjectBridgeBundle()->loadExtension([], $this->configurator($container), $container);

        self::assertTrue($container->hasDefinition(ValidValueObjectValidator::class));
        self::assertArrayHasKey(
            'validator.constraint_validator',
            $container->getDefinition(ValidValueObjectValidator::class)->getTags(),
        );
    }

    public function testValidatorCanBeSwitchedOff(): void
    {
        $container = new ContainerBuilder();

        new ValueObjectBridgeBundle()->loadExtension(['validator' => false], $this->configurator($container), $container);

        self::assertFalse($container->hasDefinition(ValidValueObjectValidator::class));
    }

    /**
     * @param list<string> $extensionAliases
     */
    private function containerWith(array $extensionAliases): ContainerBuilder
    {
        $container = new ContainerBuilder();

        foreach ($extensionAliases as $alias) {
            $container->registerExtension(new StubExtension($alias));
        }

        return $container;
    }

    /**
     * Narrows one prepended configuration entry down to its name => class map.
     *
     * @return array<string, string>
     */
    private function typeMap(mixed $config, ?string $section): array
    {
        self::assertIsArray($config);

        if ($section !== null) {
            $config = $config[$section] ?? null;
            self::assertIsArray($config);
        }

        $types = $config['types'] ?? null;
        self::assertIsArray($types);

        $map = [];

        foreach ($types as $name => $class) {
            self::assertIsString($name);
            self::assertIsString($class);
            $map[$name] = $class;
        }

        return $map;
    }

    /**
     * The bundle never reads the configurator -- it works on the ContainerBuilder
     * -- but the signature demands one, and its third argument is by reference.
     */
    private function configurator(ContainerBuilder $container): ContainerConfigurator
    {
        $instanceof = [];

        return new ContainerConfigurator(
            $container,
            new PhpFileLoader($container, new FileLocator()),
            $instanceof,
            'dummy',
            'dummy',
        );
    }
}

/** Simulates the Doctrine bridge package not being installed. */
final class BundleWithoutDoctrinePackage extends ValueObjectBridgeBundle
{
    // The alias is derived from the class name, so it must be pinned or the
    // subclass would look for configuration under its own name.
    protected string $extensionAlias = 'value_object_bridge';

    protected function ormTypes(): ?array
    {
        return null;
    }

    protected function odmTypes(): ?array
    {
        return null;
    }
}

final class StubExtension extends Extension
{
    public function __construct(private readonly string $alias)
    {
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param array<array-key, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
    }
}
