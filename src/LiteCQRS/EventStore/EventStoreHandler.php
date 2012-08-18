<?php

namespace LiteCQRS\EventStore;

use LiteCQRS\Bus\MessageHandlerInterface;
use LiteCQRS\Bus\MessageInterface;

/**
 * Event Store Handler
 *
 * This handler takes care of saving all events into an event store.
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
}

