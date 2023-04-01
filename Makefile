all: vendor tests/data

vendor: composer.json composer.lock
	composer install
	touch $@

tests/data:
	$(MAKE) -C $@ $(filter all clean,$(MAKECMDGOALS))

check: vendor tests/data
	vendor/bin/phpunit tests --coverage-filter='src'

clean: tests/data
	rm -rf vendor

.PHONY: all tests/data check clean
