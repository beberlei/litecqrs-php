<?php

namespace LiteCQRS\Plugin\CRUD;

use LiteCQRS\Plugin\CRUD\Model\Events\ResourceUpdatedEvent;

trait CrudUpdatable
{
    public function update(array $data)
    {
        $this->apply(new ResourceUpdatedEvent(array(
            'class' => get_class($this),
            'id'    => $this->id,
            'data'  => $data,
        )));
    }

    protected function applyResourceUpdated(ResourceUpdatedEvent $event)
    {
        $properties = array_keys(get_class_vars($this));

        foreach ($event->data as $key => $value) {
            if (in_array($key, $properties)) {
                $this->$key = $value;
            }
        }
    }
}

