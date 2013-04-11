<?php

namespace LiteCQRS\Bus;

class EventInvocationHandler implements MessageHandlerInterface
{
    private $service;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function handle($event)
    {
        $eventName = new EventName($event);
        $methodName = "on" . $eventName;

        $this->service->$methodName($event);
    }
}
