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
            'data'  => $this->data,
        )));
    }

    protected function applyResourceUpdated(ResourceUpdatedEvent $event)
    {
        $this->updateDomain($event);
    }
}
