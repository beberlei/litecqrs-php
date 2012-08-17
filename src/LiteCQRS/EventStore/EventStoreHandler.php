<?php

namespace LiteCQRS\EventStore;

use LiteCQRS\Bus\MessageHandlerInterface;
use LiteCQRS\Bus\MessageInterface;

/**
 * Event Store Transaction around a command handler.
 *
 * If you want to use EventSourcing then the command handler has to wrap this
 * event store handler, which passes all events from the identity map
 * containing all aggregate roots, to event store. The event store then commits
 * its transaction and publishes all the events to all listeners.
 */
class EventStoreHandler implements MessageHandlerInterface
{
    private $next;
    private $eventStore;
    private $identityMap;

    public function __construct(MessageHandlerInterface $next, EventStoreInterface $eventStore)
    {
        $this->next        = $next;
        $this->eventStore  = $eventStore;
    }

    public function handle(MessageInterface $message)
    {
        $this->eventStore->store($message);
        $this->next->handle($message);
    }

    protected function passEventsToStore()
    {
        if (!$this->identityMap) {
            return;
        }

        foreach ($this->identityMap->all() as $aggregateRoot) {
            $id = $this->identityMap->getAggregateId($aggregateRoot);
            foreach ($aggregateRoot->popAppliedEvents() as $event) {
                $header = $event->getMessageHeader();
                $header->aggregateType = get_class($aggregateRoot);
                $header->aggregateId   = $id;
                $header->setAggregate(null);

                $this->eventStore->add($event);
            }
        }
    }
}

