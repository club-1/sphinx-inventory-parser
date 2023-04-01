<?php

namespace Club1\SphinxInventoryParser;

use UnexpectedValueException;

class SphinxInventoryParser
{
	/**
	 * @param resource $stream	The ressource opened in read mode from which to parse the Sphinx inventory.
	 */
	public function parse($stream, string $baseURI = ''): SphinxInventory {
		$versionStr = fgets($stream, 30);
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
		$projectStr = fgets($stream);
		$result = sscanf($projectStr, '# Project: %s', $project);
		if ($result !== 1) {
			$str = substr($projectStr, 0, -1);
			throw new UnexpectedValueException("second line is not a valid Project string: '$str'");
		}
		$versionStr = fgets($stream);
		$result = sscanf($versionStr, '# Version: %s', $version);
		if ($result !== 1) {
			$str = substr($versionStr, 0, -1);
			throw new UnexpectedValueException("third line is not a valid Version string: '$str'");
		}
		$zlibStr = fgets($stream);
		if (!str_contains($zlibStr, 'zlib')) {
			$str = substr($zlibStr, 0, -1);
			throw new UnexpectedValueException("fourth line does advertise zlib compression: '$str'");
		}
		 // We need to skip the 2 octets of Zlib header because PHP's zlib.inflate
		 // semms to be in fact only a DEFLATE filter.
		 // See: <https://bugs.php.net/bug.php?id=68556>
		fread($stream, 2);
		stream_filter_append($stream, 'zlib.inflate', STREAM_FILTER_READ);
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
			 // @codeCoverageIgnoreStart
			$msg = error_get_last()['message'];
			throw new UnexpectedValueException("could not read until end of stream: $msg");
			 // @codeCoverageIgnoreEnd
		}
		return $inventory;
	}
}
