<?php
namespace LiteCQRS\Bus;

use LiteCQRS\DomainEvent;

/**
 * Event Message bus handles all events that were emitted by domain objects.
 *
 * The Event Message Bus finds all event handles that listen to a certain
 * event, and then triggers these handlers one after another. Exceptions in
 * event handlers should be swallowed. Intelligent Event Systems should know
 * how to retry failing events until they are successful.
 */
interface EventMessageBus
{
    public function handle(DomainEvent $event);
}

