<?php

namespace LidskaSila\Glow\EventStore\OptimisticLocking;

use LidskaSila\Glow\EventStore\EventStore;
use LidskaSila\Glow\EventStore\EventStream;
use LidskaSila\Glow\EventStore\EventStreamNotFoundException;
use LidskaSila\Glow\EventStore\Transaction;
use LidskaSila\Glow\Serializer\Serializer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class OptimisticLockingEventStore implements EventStore
{

	private $storage;

	private $serializer;

	private $eventsData = [];

	public function __construct(Storage $storage, Serializer $serializer)
	{
		$this->storage    = $storage;
		$this->serializer = $serializer;
	}

	/**
	 * @param UuidInterface $uuid
	 *
	 * @return EventStream
	 * @throws EventStreamNotFoundException
	 */
	public function find(UuidInterface $uuid)
	{
		$streamData = $this->storage->load((string) $uuid);

		if ($streamData === null) {
			throw new EventStreamNotFoundException();
		}

		$events = [];

		foreach ($streamData->getEventData() as $eventData) {
			$events[] = $this->serializer->fromArray($eventData);
		}

		return new EventStream(
			$streamData->getClassName(),
			Uuid::fromString($streamData->getId()),
			$events,
			$streamData->getVersion()
		);
	}

	/**
	 * Commit the event stream to persistence.
	 *
	 * @param EventStream $stream
	 *
	 * @return Transaction
	 */
	public function commit(EventStream $stream)
	{
		$newEvents = $stream->newEvents();

		if (count($newEvents) === 0) {
			return new Transaction($stream, $newEvents);
		}

		$id             = (string) $stream->getUuid();
		$currentVersion = (int) $stream->getVersion();
		$nextVersion    = $currentVersion + count($newEvents);

		$eventData = isset($this->eventsData[$id])
			? $this->eventsData[$id]
			: [];

		foreach ($newEvents as $newEvent) {
			$eventData[] = $this->serializer->toArray($newEvent);
		}

		$this->storage->store($id, $stream->getClassName(), $eventData, $nextVersion, $currentVersion);

		$stream->markNewEventsProcessed($nextVersion);

		return new Transaction($stream, $newEvents);
	}
}
