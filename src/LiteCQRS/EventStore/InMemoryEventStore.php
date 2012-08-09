<?php

namespace LiteCQRS\EventStore;

use LiteCQRS\Bus\EventMessageBus;
use LiteCQRS\DomainEvent;

/**
 * In Memory Event store iterates and handles all
 * events when {@see commit()} operation is called and
 * directly passes them to the event message bus.
 */
class InMemoryEventStore implements EventStoreInterface
{
    private $events          = array();
    private $eventMessageBus;

    public function __construct(EventMessageBus $messageBus)
    {
        $this->eventMessageBus = $messageBus;
    }

    public function add(DomainEvent $event)
    {
        $this->events[] = $event;
    }

    public function rollback()
    {
        $this->events = array();
    }

    public function commit()
    {
        $events = $this->events;
        $this->events = array();

        foreach ($events as $event) {
            $this->eventMessageBus->handle($event);
        }
    }
}

