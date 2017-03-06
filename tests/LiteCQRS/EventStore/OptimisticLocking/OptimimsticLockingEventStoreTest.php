<?php

namespace LidskaSila\Glow\EventStore\OptimisticLocking;

use Lidskasila\Glow\EventStore\EventStore;
use Lidskasila\Glow\EventStore\EventStoreContractTestCase;
use Lidskasila\Glow\EventStore\EventStream;
use Lidskasila\Glow\Serializer\NoopSerializer;

class OptimimsticLockingEventStoreTest extends EventStoreContractTestCase
{

	/** @var MemoryStorage */
	protected $storage;

	/** @var NoopSerializer */
	protected $serializer;

	protected function givenAnEventStore()
	{
		$this->storage    = new MemoryStorage();
		$this->serializer = new NoopSerializer();

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
