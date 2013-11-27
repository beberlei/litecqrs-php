<?php

namespace LiteCQRS\Bus;

use Exception;
use LiteCQRS\EventStore\EventStoreInterface;

class EventMessageHandler implements MessageHandlerInterface
{
    private $messageBus;
    private $next;

    public function __construct(MessageHandlerInterface $next, EventMessageBus $messageBus)
    {
        $this->next        = $next;
        $this->messageBus  = $messageBus;
    }

    public function handle($command)
    {
        $this->next->handle($command);
    }
}

