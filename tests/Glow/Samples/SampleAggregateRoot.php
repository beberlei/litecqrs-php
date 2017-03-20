<?php

namespace LidskaSila\Glow;

class SampleAggregateRoot extends AggregateRoot
{

	public $loadedFromEvents = false;

	public $foo;

	public function __construct(SampleAggregateRootId $id)
	{
		$this->apply(new SampleCreated($id, [
			'foo' => 'bar',
		]));
	}

	public function applySampleCreated(SampleCreated $event)
	{
		$this->setId($event->sampleId);
		$this->foo              = $event->foo;
		$this->loadedFromEvents = true;
	}
}
