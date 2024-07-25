DATE = $(shell date +%F)
REPO_URL = https://github.com/club-1/sphinx-inventory-parser
INTERACTIVE    = $(shell [ -t 0 ] && echo 1)
COMPOSERFLAGS +=
PHPSTANFLAGS  += $(if $(INTERACTIVE),,--no-progress) $(if $(INTERACTIVE)$(CI),--ansi,--error-format=raw)
PHPUNITFLAGS  += $(if $(INTERACTIVE)$(CI),--colors=always --coverage-text,--colors=never)
SRC            = $(wildcard src/*.php)
BUILDER       ?= html

all: vendor tests/data

vendor: composer.json
	composer install $(COMPOSERFLAGS)
	touch $@

docs/api/%.rst: src/%.php vendor
	vendor/bin/doxphp < $< | vendor/bin/doxphp2sphinx \
	| grep -vE '(php:namespace::|:var:|phpstan-consistent-constructor)' \
	> $@

docs: $(patsubst src/%.php,docs/api/%.rst,$(SRC))
	$(MAKE) -C $@ $(BUILDER)

tests/data:
	$(MAKE) -C $@ $(filter all clean,$(MAKECMDGOALS))

tests/fuzz:
	$(MAKE) -C $@ $(filter clean,$(MAKECMDGOALS))

# Create a new release
bump = echo '$2' | awk 'BEGIN{FS=OFS="."} {$$$1+=1; for (i=$1+1; i<=3; i++) $$i=0} 1'
releasepatch: V := 3
releaseminor: V := 2
releasemajor: V := 1
release%: PREVTAG = $(shell git describe --tags --abbrev=0)
release%: TAG = v$(shell $(call bump,$V,$(PREVTAG:v%=%)))
release%: CONFIRM_MSG = Create release $(TAG)
releasepatch releaseminor releasemajor: release%: .confirm check all
	sed -i CHANGELOG.md \
		-e '/^## \[unreleased\]/s/$$/\n\n## [$(TAG)] - $(DATE)/' \
		-e '/^\[unreleased\]/{s/$(PREVTAG)/$(TAG)/; s#$$#\n[$(TAG)]: $(REPO_URL)/releases/tag/$(TAG)#}'
	sed -i docs/conf.py -e '/^release/s/$(PREVTAG)/$(TAG)/'
	git add CHANGELOG.md docs/conf.py
	git commit -m $(TAG)
	git push
	git tag $(TAG)
	git push --tags

check: analyse test
	composer validate

analyse: vendor
	vendor/bin/phpstan analyse src --level 9 --configuration src/phpstan.neon $(PHPSTANFLAGS)
	vendor/bin/phpstan analyse tests --level 5 $(PHPSTANFLAGS)

test: vendor tests/data
	XDEBUG_MODE=coverage vendor/bin/phpunit tests --coverage-filter='src' $(PHPUNITFLAGS)

fuzz: vendor
	$(MAKE) -C tests/fuzz

clean: tests/data tests/fuzz
	rm -rf vendor
	rm -rf docs/api/*.rst
	rm -rf docs/_build

.confirm:
	@echo -n "$(CONFIRM_MSG)? [y/N] " && read ans && [ $${ans:-N} = y ]

.PHONY: all docs tests/data tests/fuzz releasepatch releaseminor releasemajor check analyse test fuzz clean .confirm
