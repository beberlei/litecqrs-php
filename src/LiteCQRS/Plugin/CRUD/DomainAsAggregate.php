<?php

namespace LiteCQRS\Plugin\CRUD;

use LiteCQRS\Plugin\CRUD\Model\Events\DefaultDataEvent;

trait DomainAsAggregate
{
    protected function getAccessibleProperties()
    {
        return array_keys(get_class_vars($this));
    }

    /**
     * Update domain data
     *
     * @param Model\Events\DefaultDataEvent $event
     */
    protected function updateDomain(DefaultDataEvent $event)
    {
        $properties = $this->getAccessibleProperties();
        foreach ($event->data as $key => $value) {
            if (in_array($key, $properties)) {
                $this->$key = $value;
            }
        }
    }
}
