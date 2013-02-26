<?php

namespace LiteCQRS\Bus\IdentityMap;

use LiteCQRS\Bus\EventQueue;
use LiteCQRS\Bus\IdentityMapInterface;

/**
 * Returns all events from {@see EventProviderInterface} instances
 * that are saved in the identity map.
 */
class EventProviderQueue implements EventQueue
{
    private $identityMap;

    public function __construct(IdentityMapInterface $identityMap)
    {
        $this->identityMap = $identityMap;
    }

    public function dequeueAllEvents()
    {
        $dequeueEvents = array();

        foreach ($this->identityMap->all() as $aggregateRoot) {
            $id = $this->identityMap->getAggregateId($aggregateRoot);

            foreach ($aggregateRoot->dequeueAppliedEvents() as $event) {
                $header = $event->getMessageHeader();
                $header->aggregateType = get_class($aggregateRoot);
                $header->aggregateId   = $id;
                $header->setAggregate(null);

                $dequeueEvents[] = $event;
            }
        }

        return $dequeueEvents;
    }
}
