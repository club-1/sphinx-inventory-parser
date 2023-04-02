INTERACTIVE    = $(shell [ -t 0 ] && echo 1)
COMPOSERFLAGS +=
PHPSTANFLAGS  += $(if $(INTERACTIVE),,--no-progress) $(if $(INTERACTIVE)$(CI),,--error-format=raw)
PHPUNITFLAGS  += $(if $(INTERACTIVE)$(CI),--colors --coverage-text,--colors=never)

all: vendor tests/data

vendor: composer.json
	composer install $(COMPOSERFLAGS)
	touch $@

tests/data:
	$(MAKE) -C $@ $(filter all clean,$(MAKECMDGOALS))

check: analyse test;

analyse: vendor
	vendor/bin/phpstan analyse src --level 7

test: vendor tests/data
	XDEBUG_MODE=coverage vendor/bin/phpunit tests --coverage-filter='src' $(PHPUNITFLAGS)

clean: tests/data
	rm -rf vendor

.PHONY: all tests/data check analyse test clean
