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

/**
 * Parser for Sphinx objects.inv inventory file format.
 *
 * This is the main class of the library. Each instance provide a single public
 * method :meth:`~SphinxInventoryParser::parse`, which allows to parse a stream
 * of data into a PHP object.
 */
class SphinxInventoryParser
{
	/**
	 * @var resource $stream
	 * @ignore
	 */
	protected $stream;

	/**
	 * Create a :class:`SphinxInventoryParser` for the given stream.
	 *
	 * Such a stream is usually obtained with |fopen|_, using the ``r`` mode.
	 * For example, with a remote file over HTTPS it is possible to do this::
	 *
	 *    $parser = new SphinxInventoryParser();
	 *    $stream = fopen('https://club1.fr/docs/fr/objects.inv', 'r');
	 *    $inventory = $parser->parse($stream, 'https://club1.fr/docs/fr/');
	 *    fclose($stream);
	 *
	 * .. |fopen| replace:: ``fopen``
	 * .. _fopen: https://www.php.net/manual/en/function.fopen.php
	 *
	 * @param resource	$stream		The resource to parse, opened in read mode.
	 */
	public function __construct($stream) {
		$this->stream = $stream;
	}

	/**
	 * Parse the stream into an indexed :class:`SphinxInventory` object.
	 *
	 * The :external:doc:`syntax` encode some values in a compressed form:
	 *
	 * - The :ref:`{uri}` part is encoded as a relative path that can end with
	 *   a ``$`` which needs to be replaced by the name of the object.
	 * - The :ref:`{dispname}` part can be a ``-`` when it is the identical to
	 *   the name of the object.
	 *
	 * These are expanded before creating the :class:`SphinxObjects <SphinxObject>`
	 * so that no more processing needs to be done later.
	 *
	 * @param string	$baseURI	The base string to prepend to an object's location to get its final URI.
	 *
	 * @return SphinxInventory		The inventory parsed from the stream.
	 * @throws UnexpectedValueException	If an unexpected value is encountered while parsing.
	 */
	public function parse(string $baseURI = ''): SphinxInventory {
		$versionStr = $this->ffgets($this->stream, 32);
		$result = sscanf($versionStr, '# Sphinx inventory version %d', $version);
		if ($result !== 1) {
			$str = substr($versionStr, 0, -1);
			throw new UnexpectedValueException("first line is not a valid Sphinx inventory version string: '$str'");
		}
		switch($version) {
			case 2:
				return $this->parseV2($this->stream, $baseURI);
			default:
				throw new UnexpectedValueException("unsupported Sphinx inventory version: $version");
		}
	}

	/**
	 * @param resource $stream
	 * @ignore
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
			$result = preg_match('/(?x)(.+?)\s+([^\s:]+):(\S+)\s+(-?\d+)\s+?(\S*)\s+(.*)/', $objectStr, $matches);
			if ($result !== 1) {
				$str = substr($objectStr, 0, -1);
				throw new UnexpectedValueException("object string did not match pattern: '$str'");
			}
			array_shift($matches);
			list($name, $domain, $role, $priority, $location, $displayName) = $matches;
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
	 *
	 * @throws UnexpectedValueException	If no line is readeble.
	 * @ignore
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
