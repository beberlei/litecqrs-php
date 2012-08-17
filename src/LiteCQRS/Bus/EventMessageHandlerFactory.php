<?php

namespace LiteCQRS\Bus;

use LiteCQRS\Command;
use LiteCQRS\EventStore\IdentityMapInterface;

class EventMessageHandlerFactory
{
    private $messageBus;
    private $identityMap;

    public function __construct(EventMessageBus $messageBus, IdentityMapInterface $identityMap = null)
    {
        $this->messageBus   = $messageBus;
        $this->identityMap  = $identityMap;
    }

    public function __invoke($handler)
    {
        return new EventMessageHandler($handler, $this->messageBus, $this->identityMap);
    }
}

