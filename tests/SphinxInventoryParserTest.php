<?php

use Club1\SphinxInventoryParser\SphinxInventoryParser;
use PHPUnit\Framework\TestCase;

require 'vendor/autoload.php';

final class SphinxInventoryParserTest extends TestCase
{
	public function testParseValid(): void
	{
		$stream = fopen(__DIR__ . '/data/objects.inv.valid', 'r');
		$parser = new SphinxInventoryParser();
		$inventory = $parser->parse($stream, 'https://club1.fr/docs/fr/');
		fclose($stream);
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
		$stream = fopen(__DIR__ . '/data/objects.inv.no_objects', 'r');
		$parser = new SphinxInventoryParser();
		$inventory = $parser->parse($stream);
		fclose($stream);
		$this->assertCount(0, $inventory->objects);
	}

	public function testParseSkippedLines(): void
	{
		$stream = fopen(__DIR__ . '/data/objects.inv.skipped_lines', 'r');
		$parser = new SphinxInventoryParser();
		$inventory = $parser->parse($stream);
		fclose($stream);
		$this->assertCount(1, $inventory->objects);
	}

	public function testParseEmpty(): void
	{
		$this->expectException(UnexpectedValueException::class);
		$this->expectExceptionMessage("first line is not a valid Sphinx inventory version string: ''");
		$stream = fopen(__DIR__ . '/data/objects.inv.empty', 'r');
		$parser = new SphinxInventoryParser();
		$parser->parse($stream);
		fclose($stream);
	}

	public function testParseUnsupportedInventoryVersion(): void
	{
		$this->expectException(UnexpectedValueException::class);
		$this->expectExceptionMessage("unsupported Sphinx inventory version: 0");
		$stream = fopen(__DIR__ . '/data/objects.inv.unsupported_inventory_version', 'r');
		$parser = new SphinxInventoryParser();
		$parser->parse($stream);
		fclose($stream);
	}

	public function testParseInvalidProject(): void
	{
		$this->expectException(UnexpectedValueException::class);
		$this->expectExceptionMessage("second line is not a valid Project string: '# Invalid Project: CLUB1'");
		$stream = fopen(__DIR__ . '/data/objects.inv.invalid_project', 'r');
		$parser = new SphinxInventoryParser();
		$parser->parse($stream);
		fclose($stream);
	}

	public function testParseInvalidVersion(): void
	{
		$this->expectException(UnexpectedValueException::class);
		$this->expectExceptionMessage("third line is not a valid Version string: '# Invalid Version: 42'");
		$stream = fopen(__DIR__ . '/data/objects.inv.invalid_version', 'r');
		$parser = new SphinxInventoryParser();
		$parser->parse($stream);
		fclose($stream);
	}

	public function testParseNoZlib(): void
	{
		$this->expectException(UnexpectedValueException::class);
		$this->expectExceptionMessage("fourth line does advertise zlib compression: '# The remainder of this file is not compressed.'");
		$stream = fopen(__DIR__ . '/data/objects.inv.no_zlib', 'r');
		$parser = new SphinxInventoryParser();
		$parser->parse($stream);
		fclose($stream);
	}

	public function testParseInvalidObject(): void
	{
		$this->expectException(UnexpectedValueException::class);
		$this->expectExceptionMessage("object string did not match pattern: 'invalid sphinx inventory object line'");
		$stream = fopen(__DIR__ . '/data/objects.inv.invalid_object', 'r');
		$parser = new SphinxInventoryParser();
		$parser->parse($stream);
		fclose($stream);
	}
}
