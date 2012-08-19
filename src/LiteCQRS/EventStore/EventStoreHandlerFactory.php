<?php

namespace LiteCQRS\EventStore;

/**
 * Handler Proxy Factory just for the event store. Accepts
 * another proxy factory to create a bigger handler chain.
 */
class EventStoreHandlerFactory
{
    private $eventStore;
    private $identityMap;
    private $proxyFactoy;

    public function __construct(EventStoreInterface $eventStore, IdentityMapInterface $identityMap = null)
    {
        $this->eventStore   = $eventStore;
        $this->identityMap  = $identityMap;
    }

    public function __invoke($handler)
    {
        if (!$this->eventStore) {
            return $handler;
        }

        return new EventStoreHandler($handler, $this->eventStore, $this->identityMap);
    }
}

