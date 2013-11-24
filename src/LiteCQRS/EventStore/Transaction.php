<?php

namespace LiteCQRS\EventStore;

class Transaction
{
    private $eventStream;
    private $committedEvents = array();

    public function __construct(EventStream $eventStream, array $committedEvents)
    {
        $this->eventStream = $eventStream;
        $this->committedEvents = $committedEvents;
    }

    /**
     * @return \LiteCQRS\EventStore\EventStream
     */
    public function getEventStream()
    {
        return $this->eventStream;
    }

    /**
     * @return array<LiteCQRS\DomainEvent>
     */
    public function getCommittedEvents()
    {
        return $this->committedEvents;
    }
}
