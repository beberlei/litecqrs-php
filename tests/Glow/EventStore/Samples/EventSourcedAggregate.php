<?php

namespace LidskaSila\Glow\EventStore;

use LidskaSila\Glow\AggregateRoot;

class EventSourcedAggregate extends AggregateRoot
{

	public $eventApplied = false;

	public function __construct(EventSourcedAggregateId $id)
	{
		$this->apply(new TestEvent($id, []));
	}

	protected function applyTest(TestEvent $event)
	{
		$this->setId($event->getTestId());
		$this->eventApplied = true;
	}
}
