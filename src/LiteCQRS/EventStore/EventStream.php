<?php

namespace LiteCQRS\EventStore;

use LiteCQRS\DomainEvent;

use Rhumsaa\Uuid\Uuid;
use IteratorAggregate;
use ArrayIterator;

/**
 * Representation for a stream of events sorted by occurance.
 */
class EventStream implements IteratorAggregate
{
    /**
     * @var Rhumsaa\Uuid\Uuid
     */
    private $uuid;

    /**
     * @var array<object>
     */
    private $events = array();

    /**
     * @var array<object>
     */
    private $newEvents = array();

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $revision;

    public function __construct($className, Uuid $uuid, array $events = array(), $revision = null)
    {
        $this->uuid = $uuid;
        $this->events = $events;
        $this->revision = $revision;
        $this->className = $className;
    }

    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return Rhumsaa\Uuid\Uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getRevision()
    {
        return $this->revision;
    }

    public function addEvents(array $newEvents)
    {
        foreach ($newEvents as $newEvent) {
            $this->addEvent($newEvent);
        }
    }

    public function addEvent(DomainEvent $event)
    {
        $this->events[] = $event;
        $this->newEvents[] = $event;
    }

    /**
     * @return array<DomainEvent>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->events);
    }

    /**
     * @return array<DomainEvent>
     */
    public function newEvents()
    {
        return $this->newEvents;
    }

    public function markNewEventsProcessed()
    {
        $this->newEvents = array();
    }
}
