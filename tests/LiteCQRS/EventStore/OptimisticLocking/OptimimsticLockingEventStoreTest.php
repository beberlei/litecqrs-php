<?php

namespace LiteCQRS\EventStore\OptimisticLocking;

use LiteCQRS\EventStore\EventStoreContractTestCase;
use LiteCQRS\EventStore\EventStore;
use LiteCQRS\EventStore\EventStream;
use LiteCQRS\Serializer\NoopSerializer;

class OptimimsticLockingEventStoreTest extends EventStoreContractTestCase
{
    protected $storage;
    protected $serializer;

    protected function givenAnEventStore()
    {
        $this->storage = new MemoryStorage();
        $this->serializer = new NoopSerializer();

        return new OptimisticLockingEventStore(
            $this->storage, $this->serializer
        );
    }

    protected function givenEventStoreContains(EventStore $eventStore, EventStream $eventStream)
    {
        $this->storage->store(
            (string)$eventStream->getUuid(),
            $eventStream->getClassName(),
            iterator_to_array($eventStream),
            $eventStream->getVersion(),
            null
        );
    }

    protected function thenStorageContains(EventStream $stream)
    {
        $this->assertTrue($this->storage->contains((string)$stream->getUuid()));
    }
}
