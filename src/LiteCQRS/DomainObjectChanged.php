<?php
namespace LiteCQRS;
/**
 * Generic event that can be used when lazy.
 *
 * It allows to set the event name dynamically and accepts an array of data as
 * properties. Access to non existant properties returns null through
 * {@link __get()}.
 */
class DomainObjectChanged implements DomainEvent
{
    private $eventName;

    public function __construct($eventName, array $args)
    {
        $this->eventName = $eventName;
        foreach ($args as $property => $value) {
            $this->$property = $value;
        }
    }

    public function getEventName()
    {
        return $this->eventName;
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }
        return null;
    }
}

