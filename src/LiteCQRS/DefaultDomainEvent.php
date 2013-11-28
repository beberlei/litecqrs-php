<?php
namespace LiteCQRS;

use LiteCQRS\Util;
use LiteCQRS\Eventing\EventName;

abstract class DefaultDomainEvent implements DomainEvent
{
    /**
     * @var mixed
     */
    private $aggregateId;

    public function __construct(array $data = array())
    {
        foreach ($data as $key => $value) {
            $this->assertPropertyExists($key);

            $this->$key = $value;
        }
    }

    private function assertPropertyExists($name)
    {
        if (!property_exists($this, $name)) {
            $eventName = new EventName($this);
            throw new \RuntimeException("Property " . $name . " is not a valid property on event " . $eventName);
        }
    }

    public function setAggregateId($aggregateId)
    {
        $this->aggregateId = $aggregateId;
    }

    public function getAggregateId()
    {
        return $this->aggregateId;
    }

    public function __get($name)
    {
        $this->assertPropertyExists($name);

        return $this->$name;
    }
}
