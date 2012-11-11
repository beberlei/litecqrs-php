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
        $this->updateDomain($event);
    }
}
