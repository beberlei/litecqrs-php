<?php

namespace LidskaSila\Glow\EventStore\OptimisticLocking;

use LidskaSila\Glow\EventStore\StorageContractTestCase;
use LidskaSila\Glow\Serializer\ReflectionSerializer;
use LidskaSila\Glow\Serializer\Serializer;

class MemoryStorageTest extends StorageContractTestCase
{

	/** @var MemoryStorage */
	protected $storage;

	/** @var ReflectionSerializer */
	protected $serializer;

	public function setUp()
	{
		$this->storage    = new MemoryStorage();
		$this->serializer = new ReflectionSerializer();
	}

	protected function getStorage(): Storage
	{
		return $this->storage;
	}

	protected function getStorageSerializer(): Serializer
	{
		return $this->serializer;
	}
}
