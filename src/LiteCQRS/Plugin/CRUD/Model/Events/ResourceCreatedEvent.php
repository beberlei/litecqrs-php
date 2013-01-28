<?php

namespace LiteCQRS\Plugin\CRUD\Model\Events;

class ResourceCreatedEvent extends DefaultDataEvent
{
    public $class;
    public $id;
}

