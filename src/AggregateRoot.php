<?php

namespace LidskaSila\Glow;

use LidskaSila\Glow\Eventing\EventName;
use LidskaSila\Glow\EventStore\EventStream;
use LidskaSila\Glow\Exception\BadMethodCallException;
use LidskaSila\Glow\Exception\RuntimeException;
use Ramsey\Uuid\UuidInterface;

abstract class AggregateRoot
{

	/**
	 * @var Identity
	 */
	private $id;

	/**
	 * @var DomainEvent[]
	 */
	private $events = [];

	/**
	 * @return Identity
	 */
	final public function getId()
	{
		return $this->id;
	}

	protected function setId(Identity $identity)
	{
		$this->id = $identity;
	}

	/**
	 * @return UuidInterface
	 */
	final public function getEventStreamId()
	{
		return $this->id->getUuid();
	}

	public function loadFromEventStream(EventStream $eventStream)
	{
		if ($this->events) {
			throw new RuntimeException('AggregateRoot was already created from event stream and cannot be hydrated again.');
		}

		foreach ($eventStream as $event) {
			$this->executeEvent($event);
		}
	}

	public function pullDomainEvents()
	{
		$events       = $this->events;
		$this->events = [];

		return $events;
	}

	protected function apply(DomainEvent $event)
	{
		$this->executeEvent($event);
		$this->events[] = $event;
	}

	private function executeEvent(DomainEvent $event)
	{
		$eventName = new EventName($event);
		$method    = sprintf('apply%s', (string) $eventName);

		if (!method_exists($this, $method)) {
			throw new BadMethodCallException(
				'There is no event named "' . $method . '" that can be applied to "' . get_class($this) . '". ' .
				'If you just want to emit an event without applying changes use the raise() method.'
			);
		}

		$this->$method($event);
	}
}

