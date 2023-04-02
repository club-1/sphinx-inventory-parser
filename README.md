# PHP Sphinx Inventory Parser

[![License][licensvg]][licenurl]
![PHP versions tested][phpvtsvg]
[![build status][buildsvg]][buildurl]
[![coverage report][coversvg]][coverurl]

A PHP library to parse [Sphinx documentation](https://www.sphinx-doc.org/)'s object inventory file format
as used by [intersphinx](https://www.sphinx-doc.org/en/master/usage/extensions/intersphinx.htm).
It is loosely inspired from [sphobjinv](https://github.com/bskinn/sphobjinv),
whose documentation have also been very useful
by describing the [Sphinx objects.inv v2 Syntax](https://sphobjinv.readthedocs.io/en/stable/syntax.html).

## Requirements

The only requirement is PHP >= 7.3 with [Zlib extension](https://www.php.net/manual/en/book.zlib.php)
(usually included).

## How to use it

It provides a single `parse()` function in `SphinxInventoryParser`
that takes a readable resource, typically obtained with [`fopen()`](https://www.php.net/manual/en/function.fopen.php).

```php
use Club1\SphinxInventoryParser\SphinxInventoryParser;

$parser = new SphinxInventoryParser();
$stream = fopen('https://club1.fr/docs/fr/objects.inv', 'r');
$inventory = $parser->parse($stream, 'https://club1.fr/docs/fr/');
fclose($stream);
```

See also [`tests/SphinxInventoryParserTest.php`](tests/SphinxInventoryParserTest.php) for more examples.

## Development

### Development requirements

- **make**: to manage build scripts
- **composer**: to install PHP development dependencies
- **pigz**: to build tests data

On Debian and derivatives:

    sudo apt install make composer pigz

### Build scripts

```sh
make        # Fetch development dependencies and build tests data.
make check  # Run tests.
make clean  # Clean all downloaded and generated files.
```

[licensvg]: https://img.shields.io/badge/license-LGPL--2.1--or--later-blue "GNU Lesser General Public License version 2.1 or later"
[licenurl]: LICENSE
[phpvtsvg]: https://img.shields.io/badge/php-7.3%20%7C%207.4%20%7C%208.0%20%7C%208.1%20%7C%208.2-blue "PHP versions tested"
[buildsvg]: https://img.shields.io/github/actions/workflow/status/club-1/sphinx-inventory-parser/build.yml "Unit tests & other checks"
[buildurl]: https://github.com/club-1/sphinx-inventory-parser/actions/workflows/build.yml?query=branch%3Amain
[coversvg]: https://img.shields.io/codecov/c/gh/club-1/sphinx-inventory-parser "Lines of code covered by tests"
[coverurl]: https://app.codecov.io/gh/club-1/sphinx-inventory-parser
