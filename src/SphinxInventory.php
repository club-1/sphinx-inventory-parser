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

class SphinxInventory {

	/**
	 * ``string`` -- Name of the project of the inventory.
	 *
	 * @var string $project
	 */
	public $project;

	/**
	 * ``string`` -- Version of the project of the inventory.
	 *
	 * @var string $version
	 */
	public $version;

	/**
	 * :class:`SphinxObject[] <SphinxObject>` -- List of all Sphinx objects of the inventory.
	 *
	 * @var SphinxObject[] $objects
	 */
	public $objects = [];

	/**
	 * :class:`SphinxObject[][][] <SphinxObject>` -- Tree index of the objects of the inventory.
	 *
	 * This index allows to find the objects faster. It is a multi-level hashmap
	 * indexed with the :attr:`~SphinxObject::$domain`, :attr:`~SphinxObject::$role`
	 * and :attr:`~SphinxObject::$name` of the objects.
	 * For example, the object of the glossary :any:`term` ``API`` can be retrieved like so::
	 *
	 *    $APIObject = $inventory->domains['std']['term']['API'];
	 *
	 * @var SphinxObject[][][] $domains
	 */
	public $domains = [];

	/**
	 * Basic constructor.
	 */
	public function __construct(string $project, string $version)
	{
		$this->project = $project;
		$this->version = $version;
	}

	/**
	 * Add a Sphinx object to the inventory.
	 *
	 * The object will be added both to the :attr:`~SphinxInventory::$objects` array and
	 * the :attr:`~SphinxInventory::$domains` index.
	 *
	 * @param SphinxObject $object The object to add.
	 */
	public function addObject(SphinxObject $object): void
	{
		$this->objects[] = $object;
		if (!isset($this->domains[$object->domain])) {
			$this->domains[$object->domain] = [];
		}
		if (!isset($this->domains[$object->domain][$object->role])) {
			$this->domains[$object->domain][$object->role] = [];
		}
		$this->domains[$object->domain][$object->role][$object->name] = $object;
	}
}

