Usage
=====

This is a quite simple library but it tries to be as flexible as possible,
so there are multiple ways to use it.
The simplest one is demonstrated in the :ref:`simple-example` section.

Parse objects one by one
------------------------

When dealing with a large amount of data, it could be interesting to parse
the objects one by one, instead of storing them all in memory.
This can be achieved by calling :meth:`~SphinxInventoryParser::parseHeader()`
and :meth:`~SphinxInventoryParser::parseObjects()` directly:

.. code:: php

   $stream = fopen('https://club1.fr/docs/fr/objects.inv', 'r');
   $parser = new SphinxInventoryParser($stream);
   $header = $parser->parseHeader();
   foreach($parser->parseObjects($header, 'https://club1.fr/docs/fr') as $object) {
        // do something with $object
   }
   fclose($stream);

.. warning::

   :meth:`SphinxInventoryParser::parseObjects()` is a `generator`_,
   so its execution is postponed until iteration over its result begins,
   you must thus not close the stream before the actual iteration.

   .. _Generator: https://www.php.net/manual/en/language.generators.overview.php
