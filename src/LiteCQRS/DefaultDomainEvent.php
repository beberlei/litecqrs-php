<?php
namespace LiteCQRS;

use LiteCQRS\Util;

abstract class DefaultDomainEvent implements DomainEvent
{
    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var mixed
     */
    private $aggregateId;

    public function __construct(array $data = array())
    {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key )) {
                throw new \RuntimeException("Property " . $key . " is not a valid property on event " . $this->getEventName());
            }

            $this->$key = $value;
        }

        $this->date = Util::createMicrosecondsNow();
    }

    public function getEventName()
    {
        $class = get_class($this);

        if (substr($class, -5) === "Event") {
            $class = substr($class, 0, -5);
        }

        if (strpos($class, "\\") === false) {
            return $class;
        }

        $parts = explode("\\", $class);
        return end($parts);
    }

    public function getEventDate()
    {
        return $this->date;
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
