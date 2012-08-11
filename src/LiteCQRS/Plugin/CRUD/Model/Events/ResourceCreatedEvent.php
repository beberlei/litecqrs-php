<?php

namespace LiteCQRS\Plugin\CRUD\Model\Events;

use LiteCQRS\DefaultDomainEvent;

class ResourceCreatedEvent extends DefaultDomainEvent
{
    public $class;
    public $id;
    public $data = array();
}

