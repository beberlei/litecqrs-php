<?php

namespace LiteCQRS;

abstract class AggregateRoot implements AggregateRootInterface
{
    private $appliedEvents;

    public function getAppliedEvents()
    {
        return $this->appliedEvents;
    }

    protected function apply(DomainEvent $event)
    {
        $method = sprintf('apply%s', $event->getEventName());

        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException("There is no event named '$method' that can be applied to '" . get_class($this) . "'");
        }

        $this->$method($event);
        $this->appliedEvents[] = $event;
    }

    public function loadFromHistory(array $events)
    {
        foreach ($events as $event) {
            $this->apply($event);
        }
    }

    public function clearEvents()
    {
        $this->appliedEvents = array();
    }
}


