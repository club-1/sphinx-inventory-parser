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

.. Example .. .................................................................

Simple example
--------------

The simplest way to use it is with |SphinxInventoryParser::parseFromDoc()|
that creates a |SphinxInventory| object directly from an online documentation,
based on its URL (and an optional inventory path).

.. code:: php

   use Club1\SphinxInventoryParser\SphinxInventoryParser;

   $inventory = SphinxInventoryParser::parseFromDoc('https://club1.fr/docs/fr/');


.. Documentation .. ...........................................................

For more examples on how to use this library, see `the "Usage" section`_
of the documentation.

.. _the "Usage" section: https://club-1.github.io/sphinx-inventory-parser/usage.html

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
-  **pigz**: (Optional) to build tests data, will fallback to a PHP script if not present

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

.. |SphinxInventoryParser::parseFromDoc()| replace:: |text:SphinxInventoryParser::parseFromDoc()|_
.. |text:SphinxInventoryParser::parseFromDoc()| replace:: ``SphinxInventoryParser::parseFromDoc()``
.. _text:SphinxInventoryParser::parseFromDoc(): https://club-1.github.io/sphinx-inventory-parser/api.html#SphinxInventoryParser::parseFromDoc
.. |SphinxInventory| replace:: |text:SphinxInventory|_
.. |text:SphinxInventory| replace:: ``SphinxInventory``
.. _text:SphinxInventory: https://club-1.github.io/sphinx-inventory-parser/api.html#SphinxInventory

.. |License LGPL-2.1-or-later| image:: https://img.shields.io/badge/license-LGPL--2.1--or--later-blue
   :target: LICENSE
.. |PHP versions tested| image:: https://img.shields.io/badge/php-7.3%20%7C%207.4%20%7C%208.0%20%7C%208.1%20%7C%208.2%20%7C%208.3-blue
.. |build status| image:: https://img.shields.io/github/actions/workflow/status/club-1/sphinx-inventory-parser/build.yml
   :target: https://github.com/club-1/sphinx-inventory-parser/actions/workflows/build.yml?query=branch%3Amain
.. |coverage report| image:: https://img.shields.io/codecov/c/gh/club-1/sphinx-inventory-parser
   :target: https://app.codecov.io/gh/club-1/sphinx-inventory-parser

.. |docs status| image:: https://img.shields.io/github/actions/workflow/status/club-1/sphinx-inventory-parser/docs.yml?label=docs
   :target: https://club-1.github.io/sphinx-inventory-parser/
