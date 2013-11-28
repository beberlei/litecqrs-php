<?php

namespace LiteCQRS\Bus;

use LiteCQRS\DomainEvent;
use Exception;

abstract class AbstractEventMessageBus implements EventMessageBus
{
    public function publish(DomainEvent $event)
    {
        $eventName  = new EventName($event);
        $services   = $this->getHandlers($eventName);

        foreach ($services as $service) {
            $this->invokeEventHandler($service, $eventName, $event);
        }
    }

    protected function invokeEventHandler($service, $eventName, $event)
    {
        try {
            $methodName = "on" . $eventName;

            $service->$methodName($event);
        } catch(Exception $e) {
            if ($event instanceof EventExecutionFailed) {
                return;
            }

            $this->publish(new EventExecutionFailed(array(
                "service"   => get_class($service),
                "exception" => $e,
                "event"     => $event,
            )));
        }
    }

    abstract protected function getHandlers(EventName $eventName);
}

