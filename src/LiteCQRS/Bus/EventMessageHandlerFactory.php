<?php

namespace LiteCQRS\Bus;

use LiteCQRS\EventStore\EventStoreInterface;

class EventMessageHandlerFactory
{
    private $messageBus;
    private $queue;
    private $eventStore;

    public function __construct(EventMessageBus $messageBus, EventQueue $queue = null, EventStoreInterface $eventStore = null)
    {
        $this->messageBus  = $messageBus;
        $this->queue = $queue;
        $this->eventStore  = $eventStore;
    }

    public function __invoke($handler)
    {
        return new EventMessageHandler($handler, $this->messageBus, $this->queue, $this->eventStore);
    }
}
