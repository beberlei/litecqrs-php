<?php

namespace LiteCQRS\Plugin\CRUD\Model\Events;

class ResourceUpdatedEvent extends DefaultDataEvent
{
    public $class;
    public $id;
}
