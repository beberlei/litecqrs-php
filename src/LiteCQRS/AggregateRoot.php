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
    /**
     * @var DomainEvent[]
     */
    private $appliedEvents = array();

    public function getAppliedEvents()
    {
        return $this->appliedEvents;
    }

    public function dequeueAppliedEvents()
    {
        $events = $this->appliedEvents;
        $this->appliedEvents = array();
        return $events;
    }

    protected function apply(DomainEvent $event)
    {
        $this->executeEvent($event);
        $event->getMessageHeader()->setAggregate($this);
        $this->appliedEvents[] = $event;
    }

    private function executeEvent(DomainEvent $event)
    {
        $method = sprintf('apply%s', $event->getEventName());

        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException(
                "There is no event named '$method' that can be applied to '" . get_class($this) . "'. " .
                "If you just want to emit an event without appliying changes use the raise() method."
            );
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


