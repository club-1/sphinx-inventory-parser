DATE = $(shell date +%F)
REPO_URL = https://github.com/club-1/sphinx-inventory-parser
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
	vendor/bin/doxphp < $< | vendor/bin/doxphp2sphinx \
	| grep -vE '(php:namespace::|:var:)' \
	> $@

docs: $(patsubst src/%.php,docs/api/%.rst,$(SRC))
	$(MAKE) -C $@ html

tests/data:
	$(MAKE) -C $@ $(filter all clean,$(MAKECMDGOALS))

# Create a new release
releasepatch: V := patch
releaseminor: V := minor
releasemajor: V := major
release%: PREVTAG = $(shell git describe --tags --abbrev=0)
release%: TAG = v$(shell semver -i $V $(PREVTAG))
release%: CONFIRM_MSG = Create release $(TAG)
releasepatch releaseminor releasemajor: release%: .confirm check all
	sed -i CHANGELOG.md \
		-e '/^## \[unreleased\]/s/$$/\n\n## [$(TAG)] - $(DATE)/' \
		-e '/^\[unreleased\]/{s/$(PREVTAG)/$(TAG)/; s#$$#\n[$(TAG)]: $(REPO_URL)/releases/tag/$(TAG)#}'
	git add CHANGELOG.md
	git commit -m $(TAG)
	git push
	git tag $(TAG)
	git push --tags

check: analyse test;

analyse: vendor
	vendor/bin/phpstan analyse src --level 9 $(PHPSTANFLAGS)

test: vendor tests/data
	XDEBUG_MODE=coverage vendor/bin/phpunit tests --coverage-filter='src' $(PHPUNITFLAGS)

clean: tests/data
	rm -rf vendor
	rm -rf docs/api/*.rst
	rm -rf docs/_build

.confirm:
	@echo -n "$(CONFIRM_MSG)? [y/N] " && read ans && [ $${ans:-N} = y ]

.PHONY: all docs tests/data releasepatch releaseminor releasemajor check analyse test clean .confirm
