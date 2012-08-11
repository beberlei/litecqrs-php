<?php
namespace LiteCQRS\Plugin\CRUD\Model\Events;

use LiteCQRS\DefaultDomainEvent;

class ResourceDeletedEvent extends DefaultDomainEvent
{
    public $class;
    public $id;
}

