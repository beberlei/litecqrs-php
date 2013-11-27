<?php

namespace LiteCQRS\Bus;

use LiteCQRS\EventStore\EventStoreInterface;

class EventMessageHandlerFactory
{
    private $messageBus;

    public function __construct(EventMessageBus $messageBus)
    {
        $this->messageBus  = $messageBus;
    }

    public function __invoke($handler)
    {
        return new EventMessageHandler($handler, $this->messageBus);
    }
}

