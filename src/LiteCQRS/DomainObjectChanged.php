<?php
namespace LiteCQRS;

use LiteCQRS\Bus\EventMessageHeader;

/**
 * Generic event that can be used when lazy.
 *
 * It allows to set the event name dynamically and accepts an array of data as
 * properties. Access to non existent properties returns null through
 * {@link __get()}.
 */
class DomainObjectChanged implements DomainEvent
{
    /**
     * @var MessageHeader
     */
    private $messageHeader;

    private $eventName;

    public function __construct($eventName, array $args = array())
    {
        $this->eventName = $eventName;
        foreach ($args as $property => $value) {
            $this->$property = $value;
        }
        $this->messageHeader = new EventMessageHeader();
    }

    public function getEventName()
    {
        return $this->eventName;
    }

    public function __get($name)
    {
        if (!isset($this->$name)) {
            throw new \RuntimeException("Property $name does not exist on event " . $this->getEventName());
        }

        return $this->$name;
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
