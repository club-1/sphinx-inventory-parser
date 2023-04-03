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

use UnexpectedValueException;

class SphinxInventoryParser
{
	/**
	 * Parse a readable stream into an indexed SphinxInventory object.
	 *
	 * @param resource	$stream		The resource to parse, opened in read mode.
	 * @throws UnexpectedValueException	If an unexpected value is encountered while parsing.
	 */
	public function parse($stream, string $baseURI = ''): SphinxInventory {
		$versionStr = $this->ffgets($stream, 32);
		$result = sscanf($versionStr, '# Sphinx inventory version %d', $version);
		if ($result !== 1) {
			$str = substr($versionStr, 0, -1);
			throw new UnexpectedValueException("first line is not a valid Sphinx inventory version string: '$str'");
		}
		switch($version) {
			case 2:
				return $this->parseV2($stream, $baseURI);
			default:
				throw new UnexpectedValueException("unsupported Sphinx inventory version: $version");
		}
	}

	/**
	 * @param resource $stream
	 */
	protected function parseV2($stream, string $baseURI): SphinxInventory {
		$projectStr = $this->ffgets($stream);
		$result = sscanf($projectStr, '# Project: %s', $project);
		if ($result !== 1) {
			$str = substr($projectStr, 0, -1);
			throw new UnexpectedValueException("second line is not a valid Project string: '$str'");
		}
		assert(is_string($project), '$project must be a string');
		$versionStr = $this->ffgets($stream);
		$result = sscanf($versionStr, '# Version: %s', $version);
		if ($result !== 1) {
			$str = substr($versionStr, 0, -1);
			throw new UnexpectedValueException("third line is not a valid Version string: '$str'");
		}
		assert(is_string($version), '$version must be a string');
		$zlibStr = $this->ffgets($stream);
		if (strpos($zlibStr, 'zlib') === false) {
			$str = substr($zlibStr, 0, -1);
			throw new UnexpectedValueException("fourth line does advertise zlib compression: '$str'");
		}
		// We need to set window to 15 because PHP's zlib.inflate filter
		// implements multiple formats depending on its value, and the
		// default is in fact -15.
		// See: <https://bugs.php.net/bug.php?id=71396>
		stream_filter_append($stream, 'zlib.inflate', STREAM_FILTER_READ, ['window' => 15]);
		$inventory = new SphinxInventory($project, $version);
		while(($objectStr = fgets($stream)) !== false) {
			if (strlen($objectStr) == 1 || $objectStr[0] == '#') {
				continue;
			}
			$result = preg_match('/(?x)(.+?)\s+(\S+):(\S+)\s+(-?\d+)\s+?(\S*)\s+(.*)/', $objectStr, $matches);
			if ($result !== 1) {
				$str = substr($objectStr, 0, -1);
				throw new UnexpectedValueException("object string did not match pattern: '$str'");
			}
			list($_, $name, $domain, $role, $priority, $location, $displayName) = $matches;
			if ($location[-1] == '$') {
				$location = substr($location, 0, -1) . $name;
			}
			$uri  = $baseURI . $location;
			if ($displayName == '-') {
				$displayName = $name;
			}
			$inventory->addObject(new SphinxObject($name, $domain, $role, intval($priority), $uri, $displayName));
		}
		if (!feof($stream)) {
			throw new UnexpectedValueException('could not read until end of stream'); // @codeCoverageIgnore
		}
		return $inventory;
	}

	/**
	 * Wrapper arounf fgets that expects a line to be readable.
	 *
	 * @param resource		$stream		The resource to read from.
	 * @param int<0, max>|null	$length		Max length to read.
	 * @throws UnexpectedValueException	If no line is readeble.
	 */
	protected function ffgets($stream, ?int $length = null): string
	{
		$line = is_null($length) ? fgets($stream) : fgets($stream, $length);
		if ($line === false) {
			throw new UnexpectedValueException('unexpected end of file');
		}
		return $line;
	}
}
