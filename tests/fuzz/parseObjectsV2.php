<?php

use Club1\SphinxInventoryParser\SphinxInventoryHeader;
use Club1\SphinxInventoryParser\SphinxInventoryParser;


/** @var PhpFuzzer\Config $config */
$config->setTarget(function(string $input) {
	$stream = fopen('php://memory','r+');
	fwrite($stream, $input);
	rewind($stream);

	$parser = new SphinxInventoryParser($stream);
	$header = new SphinxInventoryHeader(2);
	$objects = $parser->parseObjects($header);
	foreach($objects as $_) {
		// Do nothing.
	}

	fclose($stream);
});

// Optional: Many targets don't exhibit bugs on large inputs that can't also be
//           produced with small inputs. Limiting the length may improve performance.
$config->setMaxLen(1024);

// Optional: Set a more restrictive list of allowed Exceptions.
$config->setAllowedExceptions([RuntimeException::class]);
