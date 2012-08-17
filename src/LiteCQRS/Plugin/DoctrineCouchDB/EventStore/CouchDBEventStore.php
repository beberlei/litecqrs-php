<?php

namespace LiteCQRS\Plugin\DoctrineCouchDB\EventStore;

use LiteCQRS\EventStore\EventStoreInterface;
use LiteCQRS\DomainEvent;

class CouchDBEventStore implements EventStoreInterface
{
    public function store(DomainEvent $event)
    {
        if ($this->seenEvents->contains($event)) {
            return;
        }

        $this->seenEvents->attach($event);
        $this->events[] = $event;
    }
}

