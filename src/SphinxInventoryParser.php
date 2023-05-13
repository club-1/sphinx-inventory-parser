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

use Generator;
use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;

/**
 * Parser for Sphinx objects.inv inventory file format.
 *
 * This is the main class of the library. It provides a static method :meth:`parseFromDoc()`
 * that should handle the most frequent use case.
 * For more control, it can be instanciated, each instance then provide a public method
 * :meth:`parse()`, which allows to parse a stream of data into a PHP object.
 *
 * For even more control over the parsing, the underlying methods :meth:`parseHeader()`
 * and :meth:`parseObjects()` can be used directly.
 *
 * @phpstan-consistent-constructor
 */
class SphinxInventoryParser
{
	/**
	 * Parse a Sphinx inventory directly from an online documentation's URL.
	 *
	 * This is the simplest way to use this library. Its parameters are
	 * similar to Sphinx's :ref:`intersphinx_mapping` configuration value.
	 * Example::
	 *
	 *    $inventory = SphinxInventoryParser::parseFromDoc('https://club1.fr/docs/');
	 *
	 * @param string $url The URL of the documentation's root, with or without the trailing slash.
	 * It is used both as the location to fetch the inventory and as the base for the :attr:`SphinxObject::$uri`.
	 * @param string $path The path to the inventory within the documentation. Defaults to ``objects.inv``.
	 * @return SphinxInventory		The parsed inventory.
	 * @throws UnexpectedValueException	If an unexpected value is encountered while parsing.
	 * @throws RuntimeException		If the stream can not be open.
	 */
	public static function parseFromDoc(string $url, string $path = 'objects.inv'): SphinxInventory {
		if ($url[-1] != '/') {
			$url .= '/';
		}
		$stream = @fopen($url . $path, 'r');
		if ($stream === false) {
			$error = error_get_last();
			$message = $error ? $error['message'] : 'unknown error';
			throw new RuntimeException("could not open file: $message");
		}
		$parser = new static($stream);
		$inventory = $parser->parse($url);
		fclose($stream);
		return $inventory;
	}

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
	 *    $stream = fopen('https://club1.fr/docs/fr/objects.inv', 'r');
	 *    $parser = new SphinxInventoryParser($stream);
	 *    $inventory = $parser->parse('https://club1.fr/docs/fr/');
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
		$header = $this->parseHeader();
		$inventory = new SphinxInventory($header->projectName, $header->projectVersion);
		foreach($this->parseObjects($header, $baseURI) as $object) {
			$inventory->addObject($object);
		}
		return $inventory;
	}

	/**
	 * Parse only the header of the stream.
	 *
	 * Read the first line of the stream to determine the inventory version,
	 * then, if the version is supported, parse the rest of the header.
	 *
	 * This consumes the header part of the stream, leaving only the objects
	 * part, ready to be parsed by :meth:`parseObjects()`.
	 *
	 * @return SphinxInventoryHeader	The header of the inventory.
	 * @throws UnexpectedValueException	If an unexpected value is encoutered while parsing.
	 */
	public function parseHeader(): SphinxInventoryHeader {
		$header = new SphinxInventoryHeader();
		$versionStr = $this->ffgets($this->stream, 32);
		$result = sscanf($versionStr, '# Sphinx inventory version %d', $header->version);
		if ($result !== 1) {
			$str = rtrim($versionStr, "\n\r");
			throw new UnexpectedValueException("first line is not a valid Sphinx inventory version string: '$str'");
		}
		switch($header->version) {
			case 2:
				return $this->parseHeaderV2($header);
			default:
				throw new UnexpectedValueException("unsupported Sphinx inventory version: $header->version");
		}
	}

	/**
	 * @ignore
	 */
	protected function parseHeaderV2(SphinxInventoryHeader $header): SphinxInventoryHeader {
		$projectStr = $this->ffgets($this->stream);
		$result = preg_match('/(*ANYCRLF)# Project: (.*)/', $projectStr, $matches);
		if ($result !== 1) {
			$str = rtrim($projectStr, "\n\r");
			throw new UnexpectedValueException("second line is not a valid Project string: '$str'");
		}
		$header->projectName = $matches[1];
		$versionStr = $this->ffgets($this->stream);
		$result = preg_match('/(*ANYCRLF)# Version: (.*)/', $versionStr, $matches);
		if ($result !== 1) {
			$str = rtrim($versionStr, "\n\r");
			throw new UnexpectedValueException("third line is not a valid Version string: '$str'");
		}
		$header->projectVersion = $matches[1];
		$zlibStr = $this->ffgets($this->stream);
		if (strpos($zlibStr, 'zlib') === false) {
			$str = rtrim($zlibStr, "\n\r");
			throw new UnexpectedValueException("fourth line does advertise zlib compression: '$str'");
		}
		// We need to set window to 15 because PHP's zlib.inflate filter
		// implements multiple formats depending on its value, and the
		// default is in fact -15.
		// See: <https://bugs.php.net/bug.php?id=71396>
		stream_filter_append($this->stream, 'zlib.inflate', STREAM_FILTER_READ, ['window' => 15]);
		return $header;
	}

	/**
	 * Parse the objects part of the stream.
	 *
	 * Read the stream as if it consists only of the objects part.
	 * This function assumes that nothing remains in the stream but
	 * the uncompressed list of objects. If the stream contains a
	 * standard inventory, :meth:`parseHeader()`
	 * must be called beforehand.
	 *
	 * @param SphinxInventoryHeader	$header	The header of the inventory, obtained via :meth:`parseHeader()` or manually created.
	 * @param string	$baseURI	The base string to prepend to an object's location to get its final URI.
	 *
	 * @return Generator&iterable<int,SphinxObject>	An iterator of :class:`SphinxObject` objects.
	 * @throws InvalidArgumentException	If the inventory version given in the header is unsupported.
	 * @throws UnexpectedValueException	If an unexpected value is encountered while parsing.
	 */
	public function parseObjects(SphinxInventoryHeader $header, string $baseURI = ''): Generator {
		switch($header->version) {
			case 2:
				return $this->parseObjectsV2($baseURI);
			default:
				throw new InvalidArgumentException("unsupported Sphinx inventory version: $header->version");
		}
	}

	/**
	 * @return Generator&iterable<int,SphinxObject>
	 * @ignore
	 */
	protected function parseObjectsV2(string $baseURI): Generator {
		while(($objectStr = fgets($this->stream)) !== false) {
			if (trim($objectStr) == '' || $objectStr[0] == '#') {
				continue;
			}
			$result = preg_match('/(*ANYCRLF)(?x)(.+?)\s+([^\s:]+):(\S+)\s+(-?\d+)\s+?(\S*)\s+(.*)/', $objectStr, $matches);
			if ($result !== 1) {
				$str = rtrim($objectStr, "\n\r");
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
			yield new SphinxObject($name, $domain, $role, intval($priority), $uri, $displayName);
		}
		if (!feof($this->stream)) {
			throw new UnexpectedValueException('could not read until end of stream'); // @codeCoverageIgnore
		}
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
