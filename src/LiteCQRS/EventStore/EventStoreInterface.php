<?php

namespace LiteCQRS\EventStore;

use LiteCQRS\DomainEvent;

/**
 * Store events
 */
interface EventStoreInterface
{
    /**
     * Add Event
     *
     * Makes sure that events are not added multiple times. Events
     * have identity and can only be executed once. Multiple
     * registiration of the same event is ignored.
     */
    public function store(DomainEvent $event);
}

