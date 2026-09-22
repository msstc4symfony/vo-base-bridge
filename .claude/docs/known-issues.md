# Known issues

## `class_exists` on the registrar is not enough

The registrar's type map names classes extending the Doctrine base type, and
reading the map autoloads them. A consumer who installed the Doctrine bridge
for ODM only has the registrar but no DBAL, so the ORM path would fatal. Both
halves are checked.

## `extensionAlias` is empty until `getContainerExtension()` runs

`AbstractBundle` fills it lazily. Reading `$this->extensionAlias` directly in
`prependExtension()` finds an empty string, `getExtensionConfig('')` returns
nothing, and every configuration key is silently ignored — including explicit
`false` and explicit `true`. Resolve it through `getContainerExtension()`.

A subclass inherits the derivation from its own class name, so a test double
must pin `$extensionAlias` or it will look for configuration under its own name.

## The bundle is not final

A bundle is a framework extension point, and overriding the two registrar
lookups is how the "package not installed" path is exercised without
uninstalling it.

## DBAL registers types on connection, ODM at boot

DoctrineBundle hands the prepended map to the connection factory, so
`Type::hasType()` is false right after boot and true once a connection is
built. The ODM registry fills at boot. Integration tests have to account for
the difference.

## The ORM integration kernel configures `dbal` only

Enabling the `orm` section pulls in the proxy factory, which then wants
`symfony/var-exporter` or native lazy objects. Type registration goes through
`doctrine.dbal.types` and needs none of it.

## Nested test directories claim files twice

`tests/Bundle` contains `tests/Bundle/Integration`, so without an explicit
`<exclude>` PHPUnit assigns those files to both suites and warns.

## Inherited Rector warnings

Three skip entries copied from the core are deprecated or never registered.
Noise, not failures; kept so the repositories' configs stay line-comparable.
