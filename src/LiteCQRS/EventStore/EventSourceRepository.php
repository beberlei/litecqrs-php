<?php

namespace LiteCQRS\EventStore;

use LiteCQRS\AggregateRoot;
use LiteCQRS\Repository;
use LiteCQRS\AggregateRootNotFoundException;

use Rhumsaa\Uuid\Uuid;

class EventSourceRepository implements Repository
{
    private $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @return AggregateRoot
     */
    public function find(Uuid $uuid)
    {
        try {
            $eventStream = $this->eventStore->find($uuid);
        } catch(EventStreamNotFoundException $e) {
            throw new AggregateRootNotFoundException();
        }

        try {
            $aggregateRootClass = $eventStream->getMetadata('aggregate_root_class');
        } catch(UnknownMetadataException $e) {
            throw new AggregateRootNotFoundException();
        }

        $reflClass = new \ReflectionClass($aggregateRootClass);

        $aggregateRoot = $reflClass->newInstanceWithoutConstructor();
        $aggregateRoot->loadFromEventStream($eventStream);

        return $aggregateRoot;
    }

    /**
     * @return void
     */
    public function add(AggregateRoot $object)
    {
        $eventStream = $object->getEventStream();
        $eventStream->setMetadata('aggregate_root_class', get_class($object));

        $this->eventStore->commit($eventStream);
    }

    /**
     * @return void
     */
    public function remove(AggregateRoot $object)
    {
        $eventStream = $object->getEventStream();
        $this->eventStore->delete($eventStream);
    }
}
