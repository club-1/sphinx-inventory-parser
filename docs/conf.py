# Configuration file for the Sphinx documentation builder.
#
# For the full list of built-in configuration values, see the documentation:
# https://www.sphinx-doc.org/en/master/usage/configuration.html

from sphinx.highlighting import lexers
from pygments.lexers.web import PhpLexer

# -- Project information -----------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#project-information

project = 'PHP Sphinx Inventory Parser'
copyright = '2023-2024, Nicolas Peugnet'
author = 'Nicolas Peugnet'
release = 'v1.3.0'

# -- General configuration ---------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#general-configuration

extensions = [
    'sphinxcontrib.phpdomain',
    'sphinx.ext.intersphinx',
    'myst_parser',
]

intersphinx_mapping = {
    'sphinx': ('https://www.sphinx-doc.org/en/master/', None),
    'sphobjinv': ('https://sphobjinv.readthedocs.io/en/stable/', None),
}

templates_path = ['_templates']
exclude_patterns = ['_build', 'Thumbs.db', '.DS_Store', 'api']

lexers['php'] = PhpLexer(startinline=True)
lexers['php-annotations'] = PhpLexer(startinline=True)
primary_domain = 'php'
highlight_language = 'php'

toc_object_entries_show_parents = 'hide'


# -- Options for HTML output -------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#options-for-html-output

html_theme = 'alabaster'
html_static_path = ['_static']
html_baseurl = 'https://club-1.github.io/sphinx-inventory-parser/'
html_theme_options = {
    'description': "Parse Sphinx documentation's object.inv inventory file format into an indexed SphinxInventory object.",
    'navigation_with_keys': True,
    'show_relbar_bottom': True,
    'github_user': 'club-1',
    'github_repo': 'sphinx-inventory-parser',
    'github_type': 'star',
    'github_button': True,
}
html_css_files = [
    'custom.css',
]

def setup(app):
    app.add_object_type('confval', 'confval', objname='configuration value',
                        indextemplate='pair: %s; configuration value')
