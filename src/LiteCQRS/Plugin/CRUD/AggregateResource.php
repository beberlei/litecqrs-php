<?php

namespace LiteCQRS\Plugin\CRUD;

use LiteCQRS\AggregateRoot;
use LiteCQRS\Plugin\CRUD\Model\Events\ResourceCreatedEvent;
use LiteCQRS\Plugin\CRUD\Model\Events\ResourceUpdatedEvent;
use LiteCQRS\Plugin\CRUD\Model\Events\ResourceDeletedEvent;

/**
 * Aggregate Resource Base class that helps you implement
 * CRUD in combination with CQRS. It adds the necessary
 * methods to make the ``CRUDCommandService`` work
 * on aggregate roots of this type.
 *
 * Important: By default this implementation is vulnerable
 * to mass assignment vulnerability. See {@link
 * http://chadmoran.com/posts/mass-assignment-vulnerability-isn-quo-t-just-for-rails}
 * for more details. To fix this issue implement a white
 * list of properties that are allowed to be updated using
 * the {@see getAccessibleProperties()} method.
 */
abstract class AggregateResource extends AggregateRoot
{
    /**
     * Return an array of properties that are allowed to change
     * through the create() and update() methods.
     *
     * @return array
     */
    protected function getAccessibleProperties()
    {
        return array_keys(get_class_vars($this));
    }

    public function create(array $data)
    {
        $this->apply(new ResourceCreatedEvent(array(
            'class' => get_class($this),
            'data'  => $data,
        )));
    }

    public function update(array $data)
    {
        $this->apply(new ResourceUpdatedEvent(array(
            'class' => get_class($this),
            'id'    => $this->id,
            'data'  => data,
        )));
    }

    public function remove()
    {
        $this->apply(new ResourceDeletedEvent());
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

    protected function applyResourceUpdated(ResourceUpdatedEvent $event)
    {
        $properties = $this->getAccessibleProperties();
        foreach ($event->data as $key => $value) {
            if (in_array($key, $properties)) {
                $this->$key = $value;
            }
        }
    }

    protected function applyResourceDeleted(ResourceDeletedEvent $event)
    {
    }
}

