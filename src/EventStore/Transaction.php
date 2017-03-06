<?php

namespace LidskaSila\Glow\EventStore;

use Lidskasila\Glow\DomainEvent;

class Transaction
{

	private $eventStream;

	private $committedEvents = [];

	public function __construct(EventStream $eventStream, array $committedEvents)
	{
		$this->eventStream     = $eventStream;
		$this->committedEvents = $committedEvents;
	}

	/**
	 * @return EventStream
	 */
	public function getEventStream()
	{
		return $this->eventStream;
	}

	/**
	 * @return DomainEvent[]
	 */
	public function getCommittedEvents()
	{
		return $this->committedEvents;
	}
}
