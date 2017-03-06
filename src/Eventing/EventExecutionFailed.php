<?php

namespace LidskaSila\Glow\Eventing;

use LidskaSila\Glow\DefaultDomainEvent;

class EventExecutionFailed extends DefaultDomainEvent
{

	public $service;

	public $exception;

	public $event;
}

