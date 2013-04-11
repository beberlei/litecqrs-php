<?php

namespace LiteCQRS\Plugin\CRUD;

use LiteCQRS\Plugin\CRUD\Model\Events\ResourceCreatedEvent;

trait CrudCreatable
{
    public function create(array $data)
    {
        $this->apply(new ResourceCreatedEvent(array(
            'class' => get_class($this),
            'id'    => $this->id,
            'data'  => $this->data,
        )));
    }

    protected function applyResourceCreated(ResourceCreatedEvent $event)
    {
        $properties = array_keys(get_class_vars($this));

        foreach ($event->data as $key => $value) {
            if (in_array($key, $properties)) {
                $this->$key = $value;
            }
        }
    }
}
