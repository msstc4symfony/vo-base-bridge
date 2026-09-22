# msstc4php/vo-base-symfony-bridge

Symfony Validator constraints and a bundle for
[`msstc4php/value-objects`][core].

One line in `bundles.php` registers the constraint validator and — if
[`msstc4php/vo-base-doctrine-bridge`][doctrine] happens to be installed — the
Doctrine ORM and ODM types as well.

## Install (pre-release)

Neither package is on Packagist yet. Check them out side by side:

```json
{
    "repositories": [
        { "type": "path", "url": "../vo-base", "options": { "symlink": true } }
    ],
    "require": {
        "msstc4php/vo-base-symfony-bridge": "dev-main"
    }
}
```

Doctrine is **not** required. Install the Doctrine bridge separately if you
want the field types; the bundle picks it up on its own.

## Enable the bundle

```php
// config/bundles.php
return [
    MSSTC4PHP\ValueObjectSymfonyBridge\Bundle\ValueObjectBridgeBundle::class => ['all' => true],
];
```

That is the whole setup. Each of the three groups registers when its dependency
is installed and its extension is present, and stays quiet otherwise.

## Configuration

```yaml
# config/packages/value_object_bridge.yaml
value_object_bridge:
    orm: ~        # null auto-detects, false suppresses, true demands
    odm: ~
    validator: ~
```

`true` makes a missing dependency an error at container build time instead of
silent absence — useful when a deployment must not start without the types.

## Validating raw input

```php
use MSSTC4PHP\ValueObjectSymfonyBridge\Validator\AssertEmailAddress;

#[AssertEmailAddress]
public string $email = '';
```

The constraint runs the value object's own constructor and turns its exception
into a violation, so the rule lives in one place rather than being restated as
constraint options. `ValidValueObject` does the same for any value object by
class name.

## Development

```
make check   # php -l, PHPStan level 9, cs-fixer, composer validate/audit, Rector, deptrac
make test    # PHPUnit: Validator, Bundle, Integration
make fix     # cs-fixer + Rector, applied
```

The Doctrine stack sits in `require-dev` purely for the bundle and integration
tests. A CI job removes all of it and still expects everything to load — that is
what keeps "suggest, not require" honest.

[core]: ../vo-base
[doctrine]: ../vo-base-doctrine-bridge
