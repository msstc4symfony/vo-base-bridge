# Conventions

## Neutral bundle name, on purpose

`ValueObjectBridgeBundle` and `value_object_bridge` carry no package name, even
though this package lives under `Msstc4Symfony\VoBaseBridge`. The
bundle spans both packages — it registers Doctrine types as well — so naming it
after either would mislead about the groups it controls.

## Separate namespace roots, not a shared prefix

`Msstc4Symfony\VoBaseBridge\` and
`MSSTC4PHP\ValueObjectDoctrineBridge\` rather than a common prefix. Two independent packages under one PSR-4 prefix
are legal, but then an FQCN no longer says which package brought the class, and
a partial install looks like a hole in one tree.

## Doctrine in require-dev only

The Doctrine stack and the bridge package are dev dependencies, present solely
for the bundle and integration tests. The CI isolation job removes all of them
and still expects everything to load — that job is what keeps the "suggest, not
require" claim honest.
