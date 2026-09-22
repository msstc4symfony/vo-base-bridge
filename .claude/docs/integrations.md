# Integrations

## The bundle

One line in `bundles.php`. Extension alias: `value_object_bridge`.

```yaml
value_object_bridge:
    orm: ~        # null auto-detects; false suppresses; true demands
    odm: ~
    validator: ~
```

Each group is independent:

| Value | Behaviour |
|---|---|
| `null` (default) | registers when both the dependency and the extension are present, stays quiet otherwise |
| `false` | never registers, even when everything is available |
| `true` | registers, or throws naming what is missing — the package or the extension |

`prependExtension()` writes into `doctrine.dbal.types` and `doctrine_mongodb.types`.
`loadExtension()` registers `ValidValueObjectValidator` with the
`validator.constraint_validator` tag.

The bundle never calls `TypeRegistrar::registerAll()`. Static registration
remains the route for bare Doctrine; mixing both in one process is unnecessary
and the `hasType` guard would survive it anyway.

## Symfony Validator

`ValidValueObject` plus 13 `Assert*` constraints, each naming one value object.
Validation runs the value object's own constructor and turns its exception into
a violation, so the rules live in one place rather than being restated as
constraint options.
