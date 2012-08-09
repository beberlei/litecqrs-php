<?php
namespace LiteCQRS\Bus;

use LiteCQRS\DomainEvent;

abstract class EventMessageBus
{
    abstract protected function getHandlers($eventName);

    public function handle(DomainEvent $event)
    {
        $eventName  = $event->getEventName();
        $handlers   = $this->getHandlers($eventName);
        $methodName = "on" . $eventName;

        foreach ($handlers as $handler) {
            $handler->$methodName($event);
        }
    }
}

