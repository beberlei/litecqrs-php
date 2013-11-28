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
            if (!property_exists($this, $key )) {
                $eventName = new EventName($this);
                throw new \RuntimeException("Property " . $key . " is not a valid property on event " . $eventName);
            }

            $this->$key = $value;
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
}
