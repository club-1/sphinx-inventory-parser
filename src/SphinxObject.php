<?php

namespace Club1\SphinxInventoryParser;

class SphinxObject {
	/** @var string $name */
	public $name;
	/** @var string $domain */
	public $domain;
	/** @var string $role */
	public $role;
	/** @var int $priority */
	public $priority;
	/** @var string $uri */
	public $uri;
	/** @var string $displayName */
	public $displayName;

	public function __construct(string $name, string $domain, string $role, int $priority, string $uri, string $displayName)
	{
		$this->name = $name;
		$this->domain = $domain;
		$this->role = $role;
		$this->priority = $priority;
		$this->uri = $uri;
		$this->displayName = $displayName;
	}
}

