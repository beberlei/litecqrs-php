<?php

namespace LidskaSila\Glow\EventStore;

use Ramsey\Uuid\UuidInterface;

/**
 * Stores events grouped together in streams identified by UUID.
 *
 * The EventStore is used to implement EventSourcing in LidskaSila\Glow
 * and is not neeeded otherwise.
 */
interface EventStore
{

	/**
	 * @throws EventStreamNotFoundException
	 * @return EventStream
	 */
	public function find(UuidInterface $uuid);

	/**
	 * Commit the event stream to persistence.
	 *
	 * @return Transaction
	 */
	public function commit(EventStream $stream);
}
