<?php

namespace LiteCQRS;

use Rhumsaa\Uuid\Uuid;

class SampleAggregateRoot extends AggregateRoot
{

	public $loadedFromEvents = false;

	public $foo;

	public function __construct(Uuid $uuid)
	{
		$this->setId($uuid);

		$this->apply(new SampleCreated([ 'foo' => 'bar' ]));
	}

	public function applySampleCreated(SampleCreated $event)
	{
		$this->foo              = $event->foo;
		$this->loadedFromEvents = true;
	}
}
