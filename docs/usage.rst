Usage
=====

This is a quite simple library but it tries to be as flexible as possible,
so there are multiple ways to use it.

.. include:: ../README.rst
   :start-after: .. Example
   :end-before: .. Documentation

.. |SphinxInventoryParser::parseFromDoc()| replace:: :meth:`SphinxInventoryParser::parseFromDoc()`
.. |SphinxInventory| replace:: :class:`SphinxInventory`

Using an existing stream
------------------------

Another way is to create a new :class:`SphinxInventoryParser` object
with a readable resource, typically obtained with |fopen()|_ with ``r`` mode,
then call its :meth:`~SphinxInventoryParser::parse()` method
that will return a :class:`SphinxInventory` object.

.. |fopen()| replace:: ``fopen()``
.. _fopen(): https://www.php.net/manual/en/function.fopen.php

.. code:: php

   use Club1\SphinxInventoryParser\SphinxInventoryParser;

   $stream = fopen('file:///tmp/objects.inv', 'r');
   $parser = new SphinxInventoryParser($stream);
   $inventory = $parser->parse('https://club1.fr/docs/fr/');
   fclose($stream);

Parse objects one by one
------------------------

When dealing with a large amount of data, it could be interesting to parse
the objects one by one, instead of storing them all in memory.
This can be achieved by calling :meth:`~SphinxInventoryParser::parseHeader()`
and :meth:`~SphinxInventoryParser::parseObjects()` directly:

.. code:: php

   use Club1\SphinxInventoryParser\SphinxInventoryParser;

   $stream = fopen('https://club1.fr/docs/fr/objects.inv', 'r');
   $parser = new SphinxInventoryParser($stream);
   $header = $parser->parseHeader();
   $objects = $parser->parseObjects($header, 'https://club1.fr/docs/fr/');
   foreach($objects as $object) {
        // do something with $object
   }
   // close only after iterating
   fclose($stream);

.. warning::

   :meth:`SphinxInventoryParser::parseObjects()` is a `generator`_,
   so its execution is postponed until iteration over its result begins,
   you must thus not close the stream before the actual iteration.

   .. _Generator: https://www.php.net/manual/en/language.generators.overview.php
