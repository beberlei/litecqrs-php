<?php

namespace LiteCQRS\EventStore;

use Rhumsaa\Uuid\Uuid;
use LiteCQRS\EventStore\EventStream;

/**
 * Stores events grouped together in streams identified by UUID.
 *
 * The EventStore is used to implement EventSourcing in LiteCQRS
 * and is not neeeded otherwise.
 */
interface EventStore
{
    /**
     * @return EventStream
     */
    public function find(Uuid $uuid);

    /**
     * Commit the event stream to persistence.
     */
    public function commit(EventStream $stream);

    public function delete(EventStream $stream);
}
