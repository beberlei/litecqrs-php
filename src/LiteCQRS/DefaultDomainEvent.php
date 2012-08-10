<?php
namespace LiteCQRS;

abstract class DefaultDomainEvent implements DomainEvent
{
    public function __construct(array $data = array())
    {
        foreach ($data as $key => $value) {
            if (property_exists($this->$key)) {
                $this->$key => $value;
            }
        }
    }

    public function getEventName()
    {
        $class = get_class($class);

        if (substr($class, -6) === "Event") {
            $class = substr($class, 0, -6);
        }

        if (strpos($class, "\\") === false) {
            return $class;
        }

        return substr($class, 1+strpos(strrev($class), "\\"));
    }
}

