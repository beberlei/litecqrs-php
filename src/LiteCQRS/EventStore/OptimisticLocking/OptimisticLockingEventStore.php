<?php

namespace LiteCQRS\EventStore\OptimisticLocking;

use Rhumsaa\Uuid\Uuid;
use LiteCQRS\EventStore\EventStream;
use LiteCQRS\EventStore\Transaction;
use LiteCQRS\EventStore\EventStreamNotFoundException;
use LiteCQRS\EventStore\EventStore;
use LiteCQRS\Serializer\Serializer;

class OptimisticLockingEventStore implements EventStore
{
    private $storage;
    private $serializer;
    private $eventsData = array();

    public function __construct(Storage $storage, Serializer $serializer)
    {
        $this->storage = $storage;
        $this->serializer = $serializer;
    }

    /**
     * @throws EventStreamNotFoundException
     * @return EventStream
     */
    public function find(Uuid $uuid)
    {
        $streamData = $this->storage->load((string)$uuid);

        if ($streamData === null) {
            throw new EventStreamNotFoundException();
        }

        $events = array();

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
     * @return Transaction
     */
    public function commit(EventStream $stream)
    {
        $newEvents = $stream->newEvents();

        if (count($newEvents) === 0) {
            return new Transaction($stream, $newEvents);
        }

        $id = (string)$stream->getUuid();
        $currentVersion = $stream->getVersion();
        $nextVersion = $currentVersion + count($newEvents);

        $eventData = isset($this->eventsData[$id])
            ? $this->eventsData[$id]
            : array();

        foreach ($newEvents as $newEvent) {
            $eventData[] = $this->serializer->toArray($newEvent);
        }

        $this->storage->store($id, $stream->getClassName(), $eventData, $nextVersion, $currentVersion);

        $stream->markNewEventsProcessed($nextVersion);

        return new Transaction($stream, $newEvents);
    }
}
