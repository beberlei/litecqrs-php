<?php

namespace LiteCQRS\Bus;

use LiteCQRS\DomainEvent;

class EventInvocationHandler implements MessageHandlerInterface
{
    private $service;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function handle($event)
    {
        if (!($event instanceof DomainEvent)) {
            throw new \RuntimeException("No DomainEvent instance passed to EventInvocationHandler");
        }
        $methodName = "on" . $event->getEventName();
        $this->service->$methodName($event);
    }
}

