<?php

namespace LiteCQRS\Plugin\DoctrineCouchDB\EventStore;

use LiteCQRS\EventStore\EventStoreInterface;

class CouchDBEventStore implements EventStoreInterface
{
    protected $events = array();
    protected $seenEvents;
    protected $eventMessageBus;

    public function __construct(EventMessageBus $messageBus)
    {
        $this->eventMessageBus = $messageBus;
        $this->seenEvents      = new \SplObjectStorage();
    }

    public function add(DomainEvent $event)
    {
        if ($this->seenEvents->contains($event)) {
            return;
        }

        $this->seenEvents->attach($event);
        $this->events[] = $event;
    }

    public function beginTransaction()
    {
        if ($this->events) {
            throw new \RuntimeException("There are still events on stack, cannot start new transaction. Commit first!");
        }
        $this->events = array();
    }

    public function rollback()
    {
        $this->events = array();
    }

    public function commit()
    {
        $events = $this->sort($this->events);
        $this->events = array();

        foreach ($events as $event) {
            $this->eventMessageBus->handle($event);
        }
    }

    protected function sort($events)
    {
        return $events;
    }
}

