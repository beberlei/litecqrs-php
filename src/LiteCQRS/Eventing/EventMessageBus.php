<?php

namespace LiteCQRS\Eventing;

use LiteCQRS\DomainEvent;

/**
 * Event Message bus handles all events that were emitted by domain objects.
 *
 * The Event Message Bus finds all event handles that listen to a certain
 * event, and then triggers these handlers one after another. Exceptions in
 * event handlers should be swallowed. Intelligent Event Systems should know
 * how to retry failing events until they are successful or failed too often.
 */
interface EventMessageBus
{

	/**
	 * Publish an event to the bus.
	 *
	 * @param DomainEvent $event
	 *
	 * @return void
	 */
	public function publish(DomainEvent $event);
}

