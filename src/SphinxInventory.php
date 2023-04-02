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
	/** @var string $project */
	public $project;
	/** @var string $version */
	public $version;
	/** @var SphinxObject[] $objects */
	public $objects = [];
	/** @var SphinxObject[][][] $domains */
	public $domains = [];
	

	public function __construct(string $project, string $version)
	{
		$this->project = $project;
		$this->version = $version;
	}

	public function addObject(SphinxObject $object)
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

