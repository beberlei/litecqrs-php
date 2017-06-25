<?php

namespace LidskaSila\Glow\EventStore;

use LidskaSila\Glow\AggregateRoot;
use LidskaSila\Glow\AggregateRootNotFoundException;
use LidskaSila\Glow\Eventing\EventMessageBus;
use LidskaSila\Glow\Repository;
use Ramsey\Uuid\UuidInterface;

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
	 * @param UuidInterface $uuid
	 * @param string        $className
	 * @param null          $expectedVersion
	 *
	 * @return AggregateRoot
	 * @throws AggregateRootNotFoundException
	 */
	public function find(UuidInterface $uuid, $className = null, $expectedVersion = null)
	{
		try {
			$eventStream = $this->eventStore->find($uuid);
		} catch (EventStreamNotFoundException $e) {
			throw new AggregateRootNotFoundException();
		}

		$this->streams[(string) $uuid] = $eventStream;

		$aggregateRootClass = $eventStream->getClassName();

		if (
			$className !== null
			&&
			$aggregateRootClass !== ltrim($className, '\\')
			&&
			!is_subclass_of($aggregateRootClass, $className)
		) {
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
				$object->getId()->getUuid()
			);
		}

		$eventStream = $this->streams[$id];
		$events      = $object->pullDomainEvents();
		foreach ($events as $event) {
			$event->setAggregateId($object->getId());
		}
		$eventStream->addEvents($events);

		$transaction = $this->eventStore->commit($eventStream);

		foreach ($transaction->getCommittedEvents() as $event) {
			$this->eventBus->publish($event);
		}
	}
}
