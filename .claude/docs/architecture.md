# Architecture

Two layers, enforced by `deptrac.yaml`.

```
Validator  — ValidValueObject + 13 Assert* constraints
    ^
    |
  Bundle   — ValueObjectBridgeBundle
```

There is **no Doctrine layer**. That absence is the split: the Doctrine types
live in `msstc4php/vo-base-doctrine-bridge`, which this package only *suggests*.

## Why suggest and not require

A `require` would hand 74 Doctrine types to a Symfony consumer with no Doctrine
at all, and tie the two release cycles together. The bundle reaches the
registrars behind a guard instead.

The guard has two halves, and both are needed:

1. `class_exists(Orm\TypeRegistrar)` — is the bridge package installed?
2. `class_exists(Doctrine\DBAL\Types\Type)` — is the Doctrine stack behind it?

The second is not paranoia. The registrar's map names types extending the
Doctrine base class, and reading the map autoloads them, so a consumer who
installed the bridge for ODM only would fatal on the ORM path without it.

## Autoload safety

Proven per push, not asserted: CI removes the bridge package *and* the entire
Doctrine stack, then loads every class and runs the suites that do not need
Doctrine. Measured on the current tree — all 16 classes load with 40 packages
removed, and the Validator suite passes in full.

## Layout

```
src/
├── Validator/   ValidValueObject, ValidValueObjectValidator, 13 Assert*
└── Bundle/      ValueObjectBridgeBundle
```
