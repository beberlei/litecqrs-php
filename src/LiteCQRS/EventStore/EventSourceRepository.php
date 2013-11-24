<?php

namespace LiteCQRS\EventStore;

use LiteCQRS\Bus\EventMessageBus;
use LiteCQRS\AggregateRoot;
use LiteCQRS\Repository;
use LiteCQRS\AggregateRootNotFoundException;

use Rhumsaa\Uuid\Uuid;

class EventSourceRepository implements Repository
{
    private $eventStore;
    private $eventBus;

    public function __construct(EventStore $eventStore, EventMessageBus $eventBus)
    {
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
    }

    /**
     * @return AggregateRoot
     */
    public function find($className, Uuid $uuid, $expectedVersion = null)
    {
        try {
            $eventStream = $this->eventStore->find($uuid);
        } catch (EventStreamNotFoundException $e) {
            throw new AggregateRootNotFoundException();
        }

        $aggregateRootClass = $eventStream->getClassName();

        if ($aggregateRootClass !== ltrim($className, '\\')) {
            throw new AggregateRootNotFoundException();
        }

        if ($expectedVersion && $eventStream->getVersion() !== $expectedVersion) {
            throw new ConcurrencyException();
        }

        $reflClass = new \ReflectionClass($aggregateRootClass);

        $aggregateRoot = $reflClass->newInstanceWithoutConstructor();
        $aggregateRoot->loadFromEventStream($eventStream);

        return $aggregateRoot;
    }

    /**
     * @return void
     */
    public function save(AggregateRoot $object)
    {
        $eventStream = $object->getEventStream();

        $transaction = $this->eventStore->commit($eventStream);

        foreach ($transaction->getCommittedEvents() as $event) {
            $this->eventBus->publish($event);
        }
    }
}
