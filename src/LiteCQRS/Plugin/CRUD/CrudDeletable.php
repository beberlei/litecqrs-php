<?php

namespace LiteCQRS\Plugin\CRUD;

use LiteCQRS\Plugin\CRUD\Model\Events\ResourceDeletedEvent;

trait CrudDeletable
{
    public function remove()
    {
        $this->apply(new ResourceDeletedEvent());
    }

    protected function applyResourceDeleted(ResourceDeletedEvent $event)
    {
    }
}

