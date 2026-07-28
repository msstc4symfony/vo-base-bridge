PHPSTAN_CONFIG ?= phpstan.dist.neon

check: ## Check code
	find ./src ./tests -name '*.php' | xargs -r php -l
	vendor/bin/phpstan analyse --memory-limit=512M -c $(PHPSTAN_CONFIG)
	vendor/bin/php-cs-fixer check
	composer validate --strict --no-check-publish
	composer audit
	vendor/bin/rector process -n
	vendor/bin/deptrac analyse --config-file=deptrac.yaml --no-progress

test: ## Test code
	vendor/bin/phpunit

test-with-coverage: ## Test code with coverage
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html coverage

regenerate-baseline: ## Regenerate PHPStan baseline
	vendor/bin/phpstan analyse --memory-limit=512M -c $(PHPSTAN_CONFIG) -b phpstan-baseline.neon

fix: ## Fix code
	vendor/bin/php-cs-fixer fix
	vendor/bin/rector process

## Help
help: ## List of all commands
	@grep -E '(^[a-zA-Z_0-9-]+:.*?##.*$$)|(^##)' Makefile \
	| awk 'BEGIN {FS = ":.*?## "}; {printf "%-24s %s\n", $$1, $$2}' \
	| sed -e 's/## //'

.DEFAULT_GOAL := help
.PHONY: check test test-with-coverage regenerate-baseline fix help
