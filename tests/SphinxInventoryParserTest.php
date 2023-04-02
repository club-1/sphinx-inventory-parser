<?php

use Club1\SphinxInventoryParser\SphinxInventoryParser;
use PHPUnit\Framework\TestCase;

require 'vendor/autoload.php';

final class SphinxInventoryParserTest extends TestCase
{
	public function testParseValid(): void
	{
		$parser = new SphinxInventoryParser();
		$stream = fopen(__DIR__ . '/data/objects.inv.valid', 'r');
		$inventory = $parser->parse($stream, 'https://club1.fr/docs/fr/');
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
		$parser = new SphinxInventoryParser();
		$stream = fopen(__DIR__ . '/data/objects.inv.no_objects', 'r');
		$inventory = $parser->parse($stream);
		fclose($stream);
		$this->assertCount(0, $inventory->objects);
	}

	public function testParseSkippedLines(): void
	{
		$parser = new SphinxInventoryParser();
		$stream = fopen(__DIR__ . '/data/objects.inv.skipped_lines', 'r');
		$inventory = $parser->parse($stream);
		fclose($stream);
		$this->assertCount(1, $inventory->objects);
	}

	/**
	 * @dataProvider parseExceptionsProvider
	 */
	public function testParseExceptions(string $file, string $message): void
	{
		$this->expectException(UnexpectedValueException::class);
		$this->expectExceptionMessage($message);
		$parser = new SphinxInventoryParser();
		$stream = fopen(__DIR__ . "/data/$file", 'r');
		$parser->parse($stream);
		fclose($stream);
	}

	public function parseExceptionsProvider(): array
	{
		return [
			[
				'objects.inv.empty',
				"first line is not a valid Sphinx inventory version string: ''",
			],
			[
				'objects.inv.unsupported_inventory_version',
				"unsupported Sphinx inventory version: 0",
			],
			[
				'objects.inv.invalid_project',
				"second line is not a valid Project string: '# Invalid Project: CLUB1'",
			],
			[
				'objects.inv.invalid_version',
				"third line is not a valid Version string: '# Invalid Version: 42'",
			],
			[
				'objects.inv.no_zlib',
				"fourth line does advertise zlib compression: '# The remainder of this file is not compressed.'",
			],
			[
				'objects.inv.invalid_object',
				"object string did not match pattern: 'invalid sphinx inventory object line'",
			],
		];
	}
}
