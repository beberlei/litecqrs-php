<?php

namespace LiteCQRS\Bus;

use LiteCQRS\DomainEvent;
use SplObjectStorage;
use Exception;

abstract class AbstractEventMessageBus implements EventMessageBus
{
    private $events;
    private $scheduledEvents;
    private $proxyFactories;

    public function __construct(array $proxyFactories = array())
    {
        $this->proxyFactories  = $proxyFactories;
        $this->events          = new SplObjectStorage();
        $this->scheduledEvents = new SplObjectStorage();
    }

    public function publish(DomainEvent $event)
    {
        if ($this->events->contains($event)) {
            return;
        }

        $this->scheduledEvents->attach($event);
    }

    public function clear()
    {
        $this->events->addAll($this->scheduledEvents);
        $this->scheduledEvents = new SplObjectStorage();
    }

    public function dispatchEvents()
    {
        $events = $this->sort(iterator_to_array($this->scheduledEvents));
        $this->clear();

        foreach ($events as $event) {
            $this->handle($event);
        }
    }

    protected function sort($events)
    {
        usort($events, function($a, $b) {
            $ad = $a->getMessageHeader()->date;
            $bd = $b->getMessageHeader()->date;

            if ($ad == $bd) {
                return $ad->format('u') > $bd->format('u') ? 1 : -1;
            } else if ($ad > $bd) {
                return 1;
            } else {
                return -1;
            }
        });

        return $events;
    }

    protected function handle(DomainEvent $event)
    {
        $eventName  = strtolower($event->getEventName());
        $services   = $this->getHandlers($eventName);

        foreach ($services as $service) {
            $this->invokeEventHandler($service, $event);
        }
    }

    protected function invokeEventHandler($service, $event)
    {
        try {
            $handler = new EventInvocationHandler($service);

            foreach (array_reverse($this->proxyFactories) as $proxyFactory) {
                $handler = $proxyFactory($handler);
            }

            $handler->handle($event);
        } catch(Exception $e) {
            $this->handle(new EventExecutionFailed(array(
                "service"   => get_class($service),
                "exception" => $e,
                "event"     => $event,
            )));
        }
    }

    abstract protected function getHandlers($eventName);
}


