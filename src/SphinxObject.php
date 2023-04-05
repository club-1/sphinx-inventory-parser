<?php

/*
 * This file is part of club-1/sphinx-inventory-parser.
 *
 * Copyright (C) 2023 Nicolas Peugnet <nicolas@club1.fr>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this library.  If not, see <https://www.gnu.org/licenses/>.
 *
 * SPDX-License-Identifier: LGPL-2.1-or-later
 */

namespace Club1\SphinxInventoryParser;

/**
 * Data class of a Sphinx object.
 *
 * All of its properties are public as it is only meant to be a data structure.
 * The values are expanded while parsing, so no more processing need to be done.
 */
class SphinxObject {

	/**
	 * ``string`` -- Name of the object.
	 *
	 * This property, combined with :attr:`~SphinxObject::$domain` and
	 * :attr:`~SphinxObject::$role` form the identifier of the object.
	 *
	 * @var string $name
	 */
	public $name;

	/**
	 * ``string`` -- Domain of the object.
	 *
	 * See :external:doc:`usage/restructuredtext/domains` in Sphinx documentation
	 * for more information.
	 *
	 * @var string $domain
	 */
	public $domain;

	/**
	 * ``string`` -- Role of the object.
	 *
	 * See :external:doc:`usage/restructuredtext/roles` in Sphinx documentation
	 * for more information.
	 *
	 * @var string $role
	 */
	public $role;

	/**
	 * ``int`` -- Priority of the object.
	 *
	 * Used by Sphinx to order search results.
	 * See also :external:ref:`{priority}` in Sphobjinv documentation.
	 *
	 * @var int $priority
	 */
	public $priority;

	/**
	 * ``string`` -- Full URI of the object.
	 *
	 * The fully resolved URI of the object. It has been expanded by
	 * :class:`SphinxInventoryParser::parse` and can be used as is.
	 *
	 * @var string $uri
	 */
	public $uri;

	/**
	 * ``string`` -- Display name of the object.
	 *
	 * The value to use when displaying the object in a document.
	 * It is often the same as the :attr:`~SphinxObject::$name` but can differ
	 * occasionally. It has been expanded by :class:`SphinxInventoryParser::parse`
	 * and can be used as is.
	 *
	 * @var string $displayName
	 */
	public $displayName;

	/**
	 * Basic constructor.
	 *
	 * Simply assign the properties of the objects with the given values.
	 */
	public function __construct(string $name, string $domain, string $role, int $priority, string $uri, string $displayName)
	{
		$this->name = $name;
		$this->domain = $domain;
		$this->role = $role;
		$this->priority = $priority;
		$this->uri = $uri;
		$this->displayName = $displayName;
	}
}

