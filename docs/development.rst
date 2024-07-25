Development
===========

The code hosting and the issue tracker is provided by `GitHub <https://github.com/club-1/sphinx-inventory-parser>`_.

.. include:: ../README.rst
   :start-after: .. Development
   :end-before: .. Epilog

Compiling the documentation
~~~~~~~~~~~~~~~~~~~~~~~~~~~

The documentation is obviously generated using `Sphinx <https://www.sphinx-doc.org>`_,
so the requierements are:

-  **sphinx**: the documentation generator
-  **sphinxcontrib-phpdomain**: to allow documenting PHP code
-  **myst-parser**: to include the Markdown formatted changelog

On Debian and derivatives::

   sudo apt install sphinx-doc python3-sphinxcontrib.phpdomain python3-myst-parser

Then to build the documentation (see :ref:`builders` for available builders):

.. code:: sh

   make docs               # Using the default builder (html)
   make docs BUILDER=epub  # Using a specific builder
