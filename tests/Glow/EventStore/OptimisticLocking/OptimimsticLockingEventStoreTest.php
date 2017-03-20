<?php

namespace LidskaSila\Glow\EventStore\OptimisticLocking;

use LidskaSila\Glow\EventStore\EventStore;
use LidskaSila\Glow\EventStore\EventStoreContractTestCase;
use LidskaSila\Glow\EventStore\EventStream;
use LidskaSila\Glow\Serializer\NoopSerializer;
use LidskaSila\Glow\Serializer\ReflectionSerializer;

class OptimimsticLockingEventStoreTest extends EventStoreContractTestCase
{

	/** @var MemoryStorage */
	protected $storage;

	/** @var NoopSerializer */
	protected $serializer;

	protected function givenAnEventStore()
	{
		$this->storage    = new MemoryStorage();
		$this->serializer = new ReflectionSerializer();

		return new OptimisticLockingEventStore(
			$this->storage,
			$this->serializer
		);
	}

	protected function givenEventStoreContains(EventStore $eventStore, EventStream $eventStream)
	{
		$this->storage->store(
			(string) $eventStream->getUuid(),
			$eventStream->getClassName(),
			array_map([ $this->serializer, 'toArray' ], iterator_to_array($eventStream)),
			$eventStream->getVersion(),
			null
		);
	}

	protected function thenStorageContains(EventStream $stream)
	{
		self::assertTrue($this->storage->contains((string) $stream->getUuid()));
	}
}
