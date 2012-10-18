<?php
namespace LiteCQRS;

use LiteCQRS\Bus\MessageInterface;
use LiteCQRS\Bus\EventMessageHeader;

/**
 * Domain Events happen during command execution/handling.
 *
 * They are assumed be immutable objects that cannot change after instantiation.
 * Changing events can cause weird problems, so avoid this.
 *
 * You can apply events to {@see AggregateRoot} objects and they record
 * that these events have happened. You have to make sure that events
 * are processed into the {@see EventStore} before the {@see CommandBus}
 * commits all the events. This happens right before the commit, when
 * the CommandBus iterates over the {@see IdentityMapInterface} and passes
 * all applied events to the event store.
 *
 * How the Events get into the {@see IdentityMapInterface} is your job however.
 * You should hook this into your preferred persistence solution, for
 * example Doctrine, Propel or what you are going to use as primary storage.
 */
interface DomainEvent extends MessageInterface
{
    public function getEventName();
    public function getMessageHeader();
    public function getAggregateId();
}

