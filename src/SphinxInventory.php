<?php

namespace Club1\SphinxInventoryParser;

class SphinxInventory {
	/** @var string $project */
	public $project;
	/** @var string $version */
	public $version;
	/** @var SphinxObject[] $objects */
	public $objects = [];
	/** @var SphinxObject[][][] $domains */
	public $domains = [];
	

	public function __construct(string $project, string $version)
	{
		$this->project = $project;
		$this->version = $version;
	}

	public function addObject(SphinxObject $object)
	{
		$this->objects[] = $object;
		if (!isset($this->domains[$object->domain])) {
			$this->domains[$object->domain] = [];
		}
		if (!isset($this->domains[$object->domain][$object->role])) {
			$this->domains[$object->domain][$object->role] = [];
		}
		$this->domains[$object->domain][$object->role][$object->name] = $object;
	}
}

