<?php

namespace LiteCQRS\Eventing;

use LiteCQRS\DefaultDomainEvent;

class EventExecutionFailed extends DefaultDomainEvent
{

	public $service;

	public $exception;

	public $event;
}

