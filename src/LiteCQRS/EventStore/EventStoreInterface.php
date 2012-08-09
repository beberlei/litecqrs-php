<?php

namespace LiteCQRS\EventStore;

use LiteCQRS\DomainEvent;

/**
 * Act as UnitOfWork for Events.
 *
 * When a command is handled it can emit events. These events are tracked
 * by the event store and committed after the command bus successfully
 * executed a command. If a command fails, the event store transaction
 * has to be rolled back. No event from a rolled back transaction has happend
 * even if was created already.
 *
 * Deferring the execution of the events till after the command execution has
 * some very handy benefits. You can trigger events with side effects on the
 * real world (e-mail notification and other "view" changes) and bind them
 * to the success of a command.
 */
interface EventStoreInterface
{
    public function add(DomainEvent $event);
    public function beginTransaction();
    public function rollback();
    public function commit();
}

