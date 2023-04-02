COMPOSERFLAGS +=
PHPUNITFLAGS  += --color --coverage-text

all: vendor tests/data

vendor: composer.json composer.lock
	composer install $(COMPOSERFLAGS)
	touch $@

tests/data:
	$(MAKE) -C $@ $(filter all clean,$(MAKECMDGOALS))

check: vendor tests/data
	XDEBUG_MODE=coverage vendor/bin/phpunit tests --coverage-filter='src' $(PHPUNITFLAGS)

clean: tests/data
	rm -rf vendor

.PHONY: all tests/data check clean
