<?php

namespace LiteCQRS;

// This is necessary, because JMS is very aggresive about parsing stuff and
// then complaining.
use JMS\SerializerBundle\Annotation\ExclusionPolicy;

/**
 * @ExclusionPolicy("all")
 */
abstract class AggregateRoot implements AggregateRootInterface
{
    private $appliedEvents = array();

    public function getAppliedEvents()
    {
        return $this->appliedEvents;
    }

    public function popAppliedEvents()
    {
        $events = $this->appliedEvents;
        $this->appliedEvents = array();
        return $events;
    }

    protected function raise(DomainEvent $event)
    {
        $event->getMessageHeader()->setAggregate($this);
        $this->appliedEvents[] = $event;
    }

    protected function apply(DomainEvent $event)
    {
        $this->executeEvent($event);
        $this->raise($event);
    }

    private function executeEvent(DomainEvent $event)
    {
        $method = sprintf('apply%s', $event->getEventName());

        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException("There is no event named '$method' that can be applied to '" . get_class($this) . "'");
        }

        $this->$method($event);
    }

    public function loadFromHistory(array $events)
    {
        foreach ($events as $event) {
            $this->executeEvent($event);
        }
    }
}


