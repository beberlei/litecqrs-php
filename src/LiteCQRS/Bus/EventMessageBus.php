<?php
namespace LiteCQRS\Bus;

abstract class EventMessageBus implements MessageBus
{
    abstract protected function getHandlers($eventName);

    public function handle($event)
    {
        if (!($event instanceof DomainEvent)) {
            throw new \RuntimeException("No valid Domain Event given!");
        }

        $eventName  = $event->getEventName();
        $handlers   = $this->getHandlers($eventName);
        $methodName = "on" . $eventName;

        foreach ($handlers as $handler) {
            $handler->$methodName($event);
        }
    }
}

