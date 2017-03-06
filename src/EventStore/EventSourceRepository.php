<?php

namespace LidskaSila\Glow\EventStore;

use LidskaSila\Glow\AggregateRoot;
use LidskaSila\Glow\AggregateRootNotFoundException;
use LidskaSila\Glow\Eventing\EventMessageBus;
use LidskaSila\Glow\Repository;
use Ramsey\Uuid\Uuid;

class EventSourceRepository implements Repository
{

	private $eventStore;

	private $eventBus;

	private $streams = [];

	public function __construct(EventStore $eventStore, EventMessageBus $eventBus)
	{
		$this->eventStore = $eventStore;
		$this->eventBus   = $eventBus;
	}

	/**
	 * @param string $className
	 * @param Uuid   $uuid
	 * @param null   $expectedVersion
	 *
	 * @return AggregateRoot
	 * @throws AggregateRootNotFoundException
	 */
	public function find($className, Uuid $uuid, $expectedVersion = null)
	{
		try {
			$eventStream = $this->eventStore->find($uuid);
		} catch (EventStreamNotFoundException $e) {
			throw new AggregateRootNotFoundException();
		}

		$this->streams[(string) $uuid] = $eventStream;

		$aggregateRootClass = $eventStream->getClassName();

		if ($aggregateRootClass !== ltrim($className, '\\')) {
			throw new AggregateRootNotFoundException();
		}

		if ($expectedVersion && $eventStream->getVersion() !== $expectedVersion) {
			throw new ConcurrencyException();
		}

		$aggregateRoot = $this->createInstanceOfAggreagteRoot($aggregateRootClass);

		$aggregateRoot->loadFromEventStream($eventStream);

		return $aggregateRoot;
	}

	/**
	 * @param AggregateRoot $object
	 *
	 * @return void
	 */
	public function save(AggregateRoot $object)
	{
		$id = (string) $object->getId();

		if (!isset($this->streams[$id])) {
			$this->streams[$id] = new EventStream(
				get_class($object),
				$object->getId()
			);
		}

		$eventStream = $this->streams[$id];
		$eventStream->addEvents($object->pullDomainEvents());

		$transaction = $this->eventStore->commit($eventStream);

		foreach ($transaction->getCommittedEvents() as $event) {
			$event->setAggregateId($object->getId());
			$this->eventBus->publish($event);
		}
	}

	/**
	 * @param $aggregateRootClass
	 *
	 * @return AggregateRoot
	 */
	private function createInstanceOfAggreagteRoot($aggregateRootClass)
	{
		$reflClass = new \ReflectionClass($aggregateRootClass);
		/** @var AggregateRoot $aggregateRoot */
		$aggregateRoot = $reflClass->newInstanceWithoutConstructor();

		return $aggregateRoot;
	}
}
