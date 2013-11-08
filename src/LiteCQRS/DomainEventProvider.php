<?php

namespace LiteCQRS;

class DomainEventProvider implements EventProviderInterface
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

    protected function raise(DomainEvent $event)
    {
        $event->getMessageHeader()->setAggregate($this);
        $this->appliedEvents[] = $event;
    }
}

