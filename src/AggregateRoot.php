<?php

namespace Lidskasila\Glow;

use Lidskasila\Glow\Eventing\EventName;
use Lidskasila\Glow\EventStore\EventStream;
use Lidskasila\Glow\Exception\BadMethodCallException;
use Lidskasila\Glow\Exception\RuntimeException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class AggregateRoot
{

	/**
	 * @var Uuid
	 */
	private $id;

	/**
	 * @var DomainEvent[]
	 */
	private $events = [];

	protected function setId(UuidInterface $uuid)
	{
		$this->id = $uuid;
	}

	/**
	 * @return Uuid
	 */
	final public function getId()
	{
		return $this->id;
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

	public function loadFromEventStream(EventStream $eventStream)
	{
		if ($this->events) {
			throw new RuntimeException('AggregateRoot was already created from event stream and cannot be hydrated again.');
		}

		$this->setId($eventStream->getUuid());

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
}

