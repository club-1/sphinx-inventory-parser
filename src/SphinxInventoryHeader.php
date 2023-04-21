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
 * Metadata of a Sphinx inventory.
 *
 * Usually obtained vith :meth:`SphinxInventoryParser::parseHeader()`, but it
 * can also be created manually if needed.
 */
class SphinxInventoryHeader
{
	/**
	 * ``int`` -- Version of the inventory.
	 *
	 * @var int $version
	 */
	public $version;

	/**
	 * ``string`` -- The inventory's project name.
	 *
	 * @var string $projectName
	 */
	public $projectName;

	/**
	 * ``string`` -- The inventory's project version.
	 *
	 * @var string $projectVersion
	 */
	public $projectVersion;

	/**
	 * Basic constructor.
	 */
	public function __construct(int $version = 2, string $projectName = '', string $projectVersion = '')
	{
		$this->version = $version;
		$this->projectName = $projectName;
		$this->projectVersion = $projectVersion;
	}
}
