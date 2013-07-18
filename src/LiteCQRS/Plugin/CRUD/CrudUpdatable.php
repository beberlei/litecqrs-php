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

    protected function apply(DomainEvent $event)
    {
        $this->applyResourceUpdated($event);
        $event->getMessageHeader()->setAggregate($this);
        $this->appliedEvents[] = $event;
    }

    protected function applyResourceUpdated(ResourceUpdatedEvent $event)
    {
        $properties = $this->getAccessibleProperties();

        foreach ($event->data as $key => $value) {
            if (in_array($key, $properties)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Return an array of properties that are allowed to change
     * through the create() and update() methods.
     *
     * @return array
     */
    protected function getAccessibleProperties()
    {
        return array_keys(get_class_vars(get_class($this)));
    }
}
