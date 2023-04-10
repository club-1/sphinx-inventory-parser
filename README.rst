PHP Sphinx Inventory Parser
===========================

|License LGPL-2.1-or-later| |PHP versions tested| |build status| |coverage report| |docs status|

.. Introduction .. ............................................................

Sphinx Inventory Parser is PHP library
to parse `Sphinx documentation <https://www.sphinx-doc.org/>`_'s object inventory file format
as used by `intersphinx <https://www.sphinx-doc.org/en/master/usage/extensions/intersphinx.html>`_.
It is loosely inspired from `sphobjinv <https://github.com/bskinn/sphobjinv>`__,
whose documentation have also been very useful
by describing the `Sphinx objects.inv v2 Syntax <https://sphobjinv.readthedocs.io/en/stable/syntax.html>`_.

Requirements
------------

The only requirement is PHP >= 7.3 with `Zlib extension <https://www.php.net/manual/en/book.zlib.php>`_
(usually included).

Installation
------------

This library is available on `packagist <https://packagist.org/packages/club-1/sphinx-inventory-parser>`_
and can be installed via `composer <https://getcomposer.org/>`_:

.. code:: sh

   composer require club-1/sphinx-inventory-parser

.. _simple-example:

Simple example
--------------

The main function it provides is |SphinxInventoryParser::parse()|
that parses a readable resource, typically obtained with |fopen()|_
and returns a |SphinxInventory| object.


.. |fopen()| replace:: ``fopen()``

.. _fopen(): https://www.php.net/manual/en/function.fopen.php

.. code:: php

   use Club1\SphinxInventoryParser\SphinxInventoryParser;

   $stream = fopen('https://club1.fr/docs/fr/objects.inv', 'r');
   $parser = new SphinxInventoryParser($stream);
   $inventory = $parser->parse('https://club1.fr/docs/fr/');
   fclose($stream);


.. Documentation .. ...........................................................

Documentation
-------------

See the full `documentation <https://club-1.github.io/sphinx-inventory-parser/>`_
for more information, including the API reference.

Development
-----------

.. Development .. .............................................................

Development requirements
~~~~~~~~~~~~~~~~~~~~~~~~

-  **make**: to manage build scripts
-  **composer**: to install PHP development dependencies
-  **pigz**: to build tests data

On Debian and derivatives:

.. code:: shell

   sudo apt install make composer pigz

Build scripts
~~~~~~~~~~~~~

.. code:: sh

   make        # Fetch development dependencies and build tests data.
   make check  # Run tests.
   make clean  # Clean all downloaded and generated files.

.. Epilog .. ..................................................................

.. |SphinxInventoryParser::parse()| replace:: ``SphinxInventoryParser::parse()``

.. |SphinxInventory| replace:: ``SphinxInventory``

.. |License LGPL-2.1-or-later| image:: https://img.shields.io/badge/license-LGPL--2.1--or--later-blue
   :target: LICENSE
.. |PHP versions tested| image:: https://img.shields.io/badge/php-7.3%20%7C%207.4%20%7C%208.0%20%7C%208.1%20%7C%208.2-blue
.. |build status| image:: https://img.shields.io/github/actions/workflow/status/club-1/sphinx-inventory-parser/build.yml
   :target: https://github.com/club-1/sphinx-inventory-parser/actions/workflows/build.yml?query=branch%3Amain
.. |coverage report| image:: https://img.shields.io/codecov/c/gh/club-1/sphinx-inventory-parser
   :target: https://app.codecov.io/gh/club-1/sphinx-inventory-parser

.. |docs status| image:: https://img.shields.io/github/actions/workflow/status/club-1/sphinx-inventory-parser/docs.yml?label=docs
   :target: https://club-1.github.io/sphinx-inventory-parser/
