<?php

namespace LiteCQRS\Plugin\CRUD;

use LiteCQRS\DomainEvent;
use LiteCQRS\Plugin\CRUD\Model\Events\ResourceCreatedEvent;

trait CrudCreatable
{
    public function create(array $data)
    {
        $this->apply(new ResourceCreatedEvent(array(
            'class' => get_class($this),
            'id'    => $this->id,
            'data'  => $data,
        )));
    }

    protected function apply(DomainEvent $event)
    {
        $this->applyResourceCreated($event);
        $event->getMessageHeader()->setAggregate($this);
        $this->appliedEvents[] = $event;
    }

    protected function applyResourceCreated(ResourceCreatedEvent $event)
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
