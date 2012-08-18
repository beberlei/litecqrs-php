<?php

namespace LiteCQRS\Bus;

use LiteCQRS\DefaultDomainEvent;

class EventExecutionFailed extends DefaultDomainEvent
{
    public $service;
    public $exception;
    public $event;
}

