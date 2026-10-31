<?php

declare(strict_types=1);

namespace Msstc4Symfony\VoBaseBridge\Bundle;

use Doctrine\DBAL\Types\Type;
use LogicException;
use MSSTC4PHP\ValueObjectDoctrineBridge\Orm\TypeRegistrar;
use Msstc4Symfony\VoBaseBridge\Validator\ValidValueObjectValidator;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Validator\Constraint;

/**
 * Registers the value-object Doctrine types and the constraint validator.
 *
 * One line in bundles.php is the whole setup. Each of the three groups is
 * independent: a group registers when its dependency is installed and its
 * extension is present, and stays quiet otherwise.
 *
 * The Doctrine types live in a separate package this one only suggests, so the
 * gate is doubled: the registrar class has to exist (package installed) and the
 * Doctrine extension has to be there (somewhere to prepend into). Requiring the
 * package instead would hand 74 types to a Symfony consumer who has no Doctrine
 * at all, and tie the two release cycles together.
 *
 * The bundle's name is deliberately neutral: it spans both packages, so naming
 * it after either would mislead about the groups it controls.
 *
 * Not final: a bundle is a framework extension point, and overriding the two
 * registrar lookups is how the "package not installed" path gets exercised
 * without uninstalling it.
 */
class ValueObjectBridgeBundle extends AbstractBundle
{
    private const string ORM_REGISTRAR = TypeRegistrar::class;

    private const string ODM_REGISTRAR = \MSSTC4PHP\ValueObjectDoctrineBridge\Odm\TypeRegistrar::class;

    // The registrar existing is not enough. Its type map names classes that
    // extend the Doctrine base type, and reading the map autoloads them, so a
    // consumer who installed the bridge package for ODM only would fatal on the
    // ORM path. Both halves are therefore checked.
    private const string ORM_BASE = Type::class;

    private const string ODM_BASE = \Doctrine\ODM\MongoDB\Types\Type::class;

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->booleanNode('orm')->defaultNull()
                    ->info('Register the DBAL types. Null auto-detects; true fails if the Doctrine bridge or the doctrine extension is missing.')
                ->end()
                ->booleanNode('odm')->defaultNull()
                    ->info('Register the ODM types. Null auto-detects; true fails if the Doctrine bridge or the doctrine_mongodb extension is missing.')
                ->end()
                ->booleanNode('validator')->defaultNull()
                    ->info('Register the ValidValueObject constraint validator. Null auto-detects.')
                ->end()
            ->end()
        ;
    }

    public function prependExtension(ContainerConfigurator $configurator, ContainerBuilder $container): void
    {
        // extensionAlias is populated lazily, inside getContainerExtension(); read
        // before that it is still the empty string and no configuration is found.
        $alias = $this->getContainerExtension()?->getAlias() ?? '';

        /** @var array{orm?: bool|null, odm?: bool|null, validator?: bool|null} $config */
        $config = $container->getExtensionConfig($alias)[0] ?? [];

        $this->prependTypes(
            $container,
            'doctrine',
            $this->ormTypes(),
            $config['orm'] ?? null,
            'orm',
            static fn (array $types): array => ['dbal' => ['types' => $types]],
        );

        $this->prependTypes(
            $container,
            'doctrine_mongodb',
            $this->odmTypes(),
            $config['odm'] ?? null,
            'odm',
            static fn (array $types): array => ['types' => $types],
        );
    }

    /**
     * The parent declares an untyped array, so the shape cannot be narrowed in
     * the signature without breaking contravariance; it is narrowed by check.
     *
     * @param array<array-key, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $container): void
    {
        $configured = $config['validator'] ?? null;
        $requested = \is_bool($configured) ? $configured : null;
        $available = \class_exists(Constraint::class);

        if ($requested === false || ($requested === null && !$available)) {
            return;
        }

        if ($requested === true && !$available) {
            throw new LogicException('value_object_bridge.validator is enabled but symfony/validator is not installed.');
        }

        $container->register(ValidValueObjectValidator::class, ValidValueObjectValidator::class)
            ->setPublic(false)
            ->addTag('validator.constraint_validator')
        ;
    }

    /**
     * @return array<string, class-string>|null null when the Doctrine bridge package is absent
     */
    protected function ormTypes(): ?array
    {
        return $this->typesFrom(self::ORM_REGISTRAR, self::ORM_BASE);
    }

    /**
     * @return array<string, class-string>|null null when the Doctrine bridge package is absent
     */
    protected function odmTypes(): ?array
    {
        return $this->typesFrom(self::ODM_REGISTRAR, self::ODM_BASE);
    }

    /**
     * @param array<string, class-string>|null $types
     * @param callable(array<string, class-string>): array<string, mixed> $shape
     */
    private function prependTypes(
        ContainerBuilder $container,
        string $extension,
        ?array $types,
        ?bool $requested,
        string $node,
        callable $shape,
    ): void {
        if ($requested === false) {
            return;
        }

        $available = $types !== null && $container->hasExtension($extension);

        if (!$available) {
            if ($requested === true) {
                throw new LogicException(\sprintf('value_object_bridge.%s is enabled but %s.', $node, $types === null ? 'msstc4php/vo-base-doctrine-bridge or the Doctrine stack it needs is not installed' : \sprintf('the "%s" extension is not registered', $extension)));
            }

            return;
        }

        $container->prependExtensionConfig($extension, $shape($types));
    }

    /**
     * @return array<string, class-string>|null
     */
    private function typesFrom(string $registrar, string $base): ?array
    {
        if (!\class_exists($registrar) || !\class_exists($base)) {
            return null;
        }

        /** @var array<string, class-string> $types */
        $types = $registrar::types();

        return $types;
    }
}
