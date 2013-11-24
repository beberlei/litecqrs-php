<?php

namespace LiteCQRS\EventStore\OptimisticLocking;

class StreamData
{
    private $id;
    private $eventData;
    private $className;
    private $version;

    public function __construct($id, $className, $eventData, $version)
    {
        $this->id = $id;
        $this->eventData = $eventData;
        $this->className = $className;
        $this->version = $version;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getEventData()
    {
        return $this->eventData;
    }
}
