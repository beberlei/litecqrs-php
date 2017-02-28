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
     * @var Uuid
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
    private $version;

    public function __construct($className, Uuid $uuid, array $events = array(), $version = null)
    {
        $this->uuid = $uuid;
        $this->events = $events;
        $this->version = $version;
        $this->className = $className;
    }

    /**
     * Return class name
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return Uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    public function addEvents(array $events)
    {
        foreach ($events as $event) {
            $this->addEvent($event);
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

    public function markNewEventsProcessed($newVersion = null)
    {
        $this->version = $newVersion;
        $this->newEvents = array();
    }
}
