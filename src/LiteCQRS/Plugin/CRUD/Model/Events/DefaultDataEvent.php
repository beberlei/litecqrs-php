<?php

namespace LiteCQRS\Plugin\CRUD\Model\Events;

use LiteCQRS\DefaultDomainEvent;

abstract class DefaultDataEvent extends DefaultDomainEvent
{
    public $data = array();
}
