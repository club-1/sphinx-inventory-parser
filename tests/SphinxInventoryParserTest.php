<?php

/*
 * This file is part of club-1/sphinx-inventory-parser.
 *
 * Copyright (C) 2023, 2024 Nicolas Peugnet <nicolas@club1.fr>
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

use Club1\SphinxInventoryParser\SphinxInventoryHeader;
use Club1\SphinxInventoryParser\SphinxInventoryParser;
use Club1\SphinxInventoryParser\SphinxObject;
use PHPUnit\Framework\TestCase;

final class SphinxInventoryParserTest extends TestCase
{
	/**
	 * @dataProvider validObjectsV2Provider
	 */
	public function testValidObjectsV2(string $objectLine, array $objectArray): void
	{
		$object = new SphinxObject(...$objectArray);
		$stream = fopen('php://memory','r+');
		fwrite($stream, $objectLine);
		rewind($stream);
		$parser = new SphinxInventoryParser($stream);
		$header = new SphinxInventoryHeader(2);
		foreach ($parser->parseObjects($header) as $o) {
			$this->assertEquals($object, $o);
		}
		fclose($stream);
	}

	public function validObjectsV2Provider(): array
	{
		return [
			'basic' => [
				'API std:term -1 doc.html#term-$ -',
				['API', 'std', 'term', -1, 'doc.html#term-API', 'API']
			],
			'display name' => [
				'test any:ref 2 location Display Name',
				['test', 'any', 'ref', 2, 'location', 'Display Name']
			],
			'whitespace in name' => [
				'white space std:label 1 white-space -',
				['white space', 'std', 'label', 1, 'white-space', 'white space']
			],
			'colon in role' => [
				'name domain:role:colon 1 doc.html#role-colon-$ -',
				['name', 'domain', 'role:colon', 1, 'doc.html#role-colon-name', 'name']
			],
			'zero lenth location' => [
				'name domain:role 1  dispname',
				['name', 'domain', 'role', 1, '', 'dispname']
			],
		];
	}

	/**
	 * @dataProvider invalidObjectsV2Provider
	 */
	public function testInvalidObjectsV2(string $objectLine): void
	{
		$stream = fopen('php://memory','r+');
		fwrite($stream, $objectLine);
		rewind($stream);
		$parser = new SphinxInventoryParser($stream);
		$header = new SphinxInventoryHeader(2);
		$this->expectException(UnexpectedValueException::class);
		$this->expectExceptionMessage("object string did not match pattern: '$objectLine'");
		foreach ($parser->parseObjects($header) as $o) {
			trigger_error(print_r($o, true), E_USER_NOTICE);
		}
		fclose($stream);
	}

	public function invalidObjectsV2Provider(): array
	{
		return [
			'empty name' => [' domain:role 1 location dispname'],
			'empty domain' => ['name :role 1 location dispname'],
			# 'whitespace in domain' => ['name dom ain:role 1 location dispname'], // matched as 'ain' domain
			'empty role' => ['name domain: 1 location dispname'],
			'whitespace in role' => ['name domain:ro le 1 location dispname'],
			'empty priority' => ['name domain:role  location dispname'],
			'non number priority' => ['name domain:role priority location dispname'],
			'decimal priority' => ['name domain:role 1.23 location dispname'],
			# 'whitespace in location' => ['name domain:role 1 loca tion dispname'], // matched as 'loca' location
			'missing dispname' => ['name domain:role 1 location'],
		];
	}

	public function testParseValid(): void
	{
		$stream = fopen(__DIR__ . '/data/valid.inv', 'r');
		$parser = new SphinxInventoryParser($stream);
		$inventory = $parser->parse('https://club1.fr/docs/fr/');
		fclose($stream);
		$this->assertEquals('CLUB1', $inventory->project);
		$this->assertEquals('main', $inventory->version);
		$this->assertCount(334, $inventory->objects);
		$this->assertCount(1, $inventory->domains);
		$this->assertCount(5, $inventory->domains['std']);
		$this->assertCount(55, $inventory->domains['std']['term']);
		$this->assertCount(27, $inventory->domains['std']['logiciel']);
		$this->assertCount(36, $inventory->domains['std']['doc']);
		$this->assertCount(214, $inventory->domains['std']['label']);
		$this->assertCount(2, $inventory->domains['std']['commande']);
		$first = $inventory->objects[0];
		$this->assertEquals('API', $first->name);
		$this->assertEquals('std', $first->domain);
		$this->assertEquals('term', $first->role);
		$this->assertEquals(-1, $first->priority);
		$this->assertEquals('https://club1.fr/docs/fr/glossaire.html#term-API', $first->uri);
		$this->assertEquals('API', $first->displayName);
	}

	public function testParseNoObjects(): void
	{
		$stream = fopen(__DIR__ . '/data/no_objects.inv', 'r');
		$parser = new SphinxInventoryParser($stream);
		$inventory = $parser->parse();
		fclose($stream);
		$this->assertCount(0, $inventory->objects);
	}

	/**
	 * @dataProvider skippedLinesProvider
	 */
	public function testParseSkippedLines(string $file): void
	{
		$stream = fopen(__DIR__ . '/data/' . $file, 'r');
		$parser = new SphinxInventoryParser($stream);
		$inventory = $parser->parse();
		fclose($stream);
		$this->assertEquals('CLUB1', $inventory->project);
		$this->assertEquals('main', $inventory->version);
		$this->assertCount(1, $inventory->objects);
		$first = $inventory->objects[0];
		$this->assertEquals('valid', $first->name);
		$this->assertEquals('std', $first->domain);
		$this->assertEquals('object', $first->role);
		$this->assertEquals(1, $first->priority);
		$this->assertEquals('line', $first->uri);
		$this->assertEquals('valid', $first->displayName);
	}

	public function skippedLinesProvider(): array
	{
		return [
			['skipped_lines.inv'],
			['skipped_lines_lf.inv'],
			['skipped_lines_crlf.inv'],
		];
	}

	public function testParseNameWhitespace(): void
	{
		$stream = fopen(__DIR__ . '/data/name_whitespace.inv', 'r');
		$parser = new SphinxInventoryParser($stream);
		$inventory = $parser->parse();
		fclose($stream);
		$this->assertCount(1, $inventory->objects);
		$this->assertEquals('flux Web', $inventory->objects[0]->name);
	}

	/**
	 * @dataProvider parseExceptionsProvider
	 */
	public function testParseExceptions(string $file, string $message): void
	{
		$this->expectException(UnexpectedValueException::class);
		$this->expectExceptionMessage($message);
		$stream = fopen(__DIR__ . "/data/$file", 'r');
		$parser = new SphinxInventoryParser($stream);
		$parser->parse();
		fclose($stream);
	}

	/**
	 * @return string[][]
	 */
	public function parseExceptionsProvider(): array
	{
		return [
			["empty.inv", "unexpected end of file"],
			["invalid_sphinx.inv", "first line is not a valid Sphinx inventory version string: '# Invalid Sphinx inventory vers'"],
			["unsupported_inventory_version.inv", "unsupported Sphinx inventory version: 0"],
			["invalid_project.inv", "second line is not a valid Project string: '# Invalid Project: CLUB1'"],
			["invalid_version.inv", "third line is not a valid Version string: '# Invalid Version: 42'"],
			["no_zlib.inv", "fourth line does advertise zlib compression: '# The remainder of this file is not compressed.'"],
		];
	}

	public function testParseManualValid(): void
	{
		$count = 0;
		$stream = fopen(__DIR__ . "/data/valid.inv", 'r');
		$parser = new SphinxInventoryParser($stream);
		$header = $parser->parseHeader();
		foreach ($parser->parseObjects($header) as $_) {
			$count++;
		}
		$this->assertEquals(334, $count);
		fclose($stream);
	}

	public function testParseObjectsUnsupportedVersion(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("unsupported Sphinx inventory version: 3");
		$stream = fopen(__DIR__ . "/data/valid.data", 'r');
		$parser = new SphinxInventoryParser($stream);
		$header = new SphinxInventoryHeader(3);
		$parser->parseObjects($header);
		fclose($stream);
	}

	public function testParseObjectsStreamError(): void
	{
		$this->expectException(UnexpectedValueException::class);
		$this->expectExceptionMessage("could not read until end of stream: fgets():");
		$stream = fopen(__DIR__ . "/data/valid.data", 'a');
		$parser = new SphinxInventoryParser($stream);
		$header = new SphinxInventoryHeader(2);
		$objects = $parser->parseObjects($header);
		$objects->next();
		fclose($stream);
	}

	public function testParseHeaderEmptyProject(): void
	{
		$stream = fopen(__DIR__ . "/data/empty_project.header", 'r');
		$parser = new SphinxInventoryParser($stream);
		$header = $parser->parseHeader();
		$this->assertEquals('', $header->projectName);
		$this->assertEquals('', $header->projectVersion);
		fclose($stream);
	}

	public function testParseFromDocException(): void
	{
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessageMatches('#^could not open file: fopen\(data/objects.inv\): #');
		$inventory = SphinxInventoryParser::parseFromDoc('data');
	}

	public function testParseFromDocValid(): void
	{
		$inventory = SphinxInventoryParser::parseFromDoc(__DIR__ . '/data/', 'no_objects.inv');
		$this->assertCount(0, $inventory->objects);
	}
}
