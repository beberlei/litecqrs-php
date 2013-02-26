<?php

namespace LiteCQRS\Bus;

use LiteCQRS\EventStore\EventStoreInterface;

class EventMessageHandlerFactory
{
    private $messageBus;
    private $identityMap;
    private $eventStore;

    public function __construct(EventMessageBus $messageBus, IdentityMapInterface $identityMap = null, EventStoreInterface $eventStore = null)
    {
        $this->messageBus  = $messageBus;
        $this->identityMap = $identityMap;
        $this->eventStore  = $eventStore;
    }

    public function __invoke($handler)
    {
        return new EventMessageHandler($handler, $this->messageBus, $this->identityMap, $this->eventStore);
    }
}

