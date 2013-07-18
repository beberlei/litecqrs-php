<?php

namespace LiteCQRS\Plugin\CRUD;

use LiteCQRS\Plugin\CRUD\Model\Events\ResourceDeletedEvent;

trait CrudDeletable
{
    public function remove()
    {
        $this->apply(new ResourceDeletedEvent());
    }

    protected function apply(DomainEvent $event)
    {
        $this->applyResourceDeleted($event);
        $event->getMessageHeader()->setAggregate($this);
        $this->appliedEvents[] = $event;
    }

    protected function applyResourceDeleted(ResourceDeletedEvent $event)
    {
    }
}
