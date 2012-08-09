<?php

namespace LiteCQRS\EventHandler;

use LiteCQRS\DomainEvent;

class ServiceInvocationHandler implements EventHandlerInterface
{
    private $service;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function handle(DomainEvent $event)
    {
        $methodName = "on" . $event->getEventName();
        $this->service->$methodName($event);
    }
}

