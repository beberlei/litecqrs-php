<?php

namespace LiteCQRS\EventStore;

use Ramsey\Uuid\Uuid;

/**
 * Stores events grouped together in streams identified by UUID.
 *
 * The EventStore is used to implement EventSourcing in LiteCQRS
 * and is not neeeded otherwise.
 */
interface EventStore
{

	/**
	 * @throws EventStreamNotFoundException
	 * @return EventStream
	 */
	public function find(Uuid $uuid);

	/**
	 * Commit the event stream to persistence.
	 *
	 * @return Transaction
	 */
	public function commit(EventStream $stream);
}
