<?php

use Club1\SphinxInventoryParser\SphinxInventoryParser;
use PHPUnit\Framework\TestCase;

require 'vendor/autoload.php';

final class SphinxInventoryParserTest extends TestCase
{
	public function testParseValid(): void
	{
		$parser = new SphinxInventoryParser();
		$stream = fopen(__DIR__ . '/data/valid.inv', 'r');
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
		$stream = fopen(__DIR__ . '/data/no_objects.inv', 'r');
		$inventory = $parser->parse($stream);
		fclose($stream);
		$this->assertCount(0, $inventory->objects);
	}

	public function testParseSkippedLines(): void
	{
		$parser = new SphinxInventoryParser();
		$stream = fopen(__DIR__ . '/data/skipped_lines.inv', 'r');
		$inventory = $parser->parse($stream);
		fclose($stream);
		$this->assertCount(1, $inventory->objects);
	}

	public function testParseNameWhitespace(): void
	{
		$parser = new SphinxInventoryParser();
		$stream = fopen(__DIR__ . '/data/name_whitespace.inv', 'r');
		$inventory = $parser->parse($stream);
		fclose($stream);
		$this->assertCount(1, $inventory->objects);
		$this->assertEquals('flux Web', $inventory->objects[0]->name);
	}

	public function testParseRoleColon(): void
	{
		$parser = new SphinxInventoryParser();
		$stream = fopen(__DIR__ . '/data/role_colon.inv', 'r');
		$inventory = $parser->parse($stream);
		fclose($stream);
		$this->assertCount(1, $inventory->objects);
		$this->assertEquals('domain', $inventory->objects[0]->domain);
		$this->assertEquals('role:colon', $inventory->objects[0]->role);
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

	/**
	 * @return string[][]
	 */
	public function parseExceptionsProvider(): array
	{
		return [
			["empty.inv", "unexpected end of file"],
			["invalid_sphinx.inv", "first line is not a valid Sphinx inventory version string: '# Invalid Sphinx inventory ver'"],
			["unsupported_inventory_version.inv", "unsupported Sphinx inventory version: 0"],
			["invalid_project.inv", "second line is not a valid Project string: '# Invalid Project: CLUB1'"],
			["invalid_version.inv", "third line is not a valid Version string: '# Invalid Version: 42'"],
			["no_zlib.inv", "fourth line does advertise zlib compression: '# The remainder of this file is not compressed.'"],
			["invalid_object.inv", "object string did not match pattern: 'invalid sphinx inventory object line'"],
		];
	}
}
