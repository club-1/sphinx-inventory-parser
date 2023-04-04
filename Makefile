INTERACTIVE    = $(shell [ -t 0 ] && echo 1)
COMPOSERFLAGS +=
PHPSTANFLAGS  += $(if $(INTERACTIVE),,--no-progress) $(if $(INTERACTIVE)$(CI),--ansi,--error-format=raw)
PHPUNITFLAGS  += $(if $(INTERACTIVE)$(CI),--colors=always --coverage-text,--colors=never)
SRC            = $(wildcard src/*)

all: vendor tests/data

vendor: composer.json
	composer install $(COMPOSERFLAGS)
	touch $@

docs/api/%.rst: src/%.php vendor
	(echo $*; echo $* | sed "s/./=/g") > $@
	vendor/bin/doxphp < $< | vendor/bin/doxphp2sphinx | tail -n+2 >> $@

docs: $(patsubst src/%.php,docs/api/%.rst,$(SRC))
	$(MAKE) -C $@ html

tests/data:
	$(MAKE) -C $@ $(filter all clean,$(MAKECMDGOALS))

check: analyse test;

analyse: vendor
	vendor/bin/phpstan analyse src --level 9 $(PHPSTANFLAGS)

test: vendor tests/data
	XDEBUG_MODE=coverage vendor/bin/phpunit tests --coverage-filter='src' $(PHPUNITFLAGS)

clean: tests/data
	rm -rf vendor
	rm -rf docs/api/*.rst
	rm -rf docs/_build

.PHONY: all docs tests/data check analyse test clean
