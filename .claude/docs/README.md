# Internal knowledge base

Engineer notes for `msstc4php/vo-base-symfony-bridge`.

| File | What it answers |
|---|---|
| [architecture.md](architecture.md) | Two layers, why the Doctrine dependency is a suggestion, and why the guard has two halves |
| [integrations.md](integrations.md) | Bundle configuration and what each group does |
| [conventions.md](conventions.md) | The neutral bundle name, separate namespace roots, Doctrine as a dev-only dependency |
| [known-issues.md](known-issues.md) | The lazy `extensionAlias`, DBAL registering on connection rather than boot, nested test suites |

Related: the core's `.claude/docs/`, and
`../vo-base-doctrine-bridge/.claude/docs/` for the types themselves.
