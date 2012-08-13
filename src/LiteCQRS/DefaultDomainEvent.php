<?php
namespace LiteCQRS;

use LiteCQRS\Bus\EventMessageHeader;

abstract class DefaultDomainEvent implements DomainEvent
{
    /**
     * @var MessageHeader
     */
    private $messageHeader;

    public function __construct(array $data = array())
    {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key )) {
                throw new \RuntimeException("Property " . $key . " is not a valid property on event " . $this->getEventName());
            }

            $this->$key = $value;
        }
        $this->messageHeader = new EventMessageHeader();
    }

    public function getEventName()
    {
        $class = get_class($this);

        if (substr($class, -6) === "Event") {
            $class = substr($class, 0, -6);
        }

        if (strpos($class, "\\") === false) {
            return $class;
        }

        $parts = explode("\\", $class);
        return end($parts);
    }

    public function getMessageHeader()
    {
        return $this->messageHeader;
    }

    public function getAggregateId()
    {
        return $this->messageHeader->aggregateId;
    }
}

