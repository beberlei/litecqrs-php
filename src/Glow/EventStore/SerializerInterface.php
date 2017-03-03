<?php

namespace Lidskasila\Glow\EventStore;

use Lidskasila\Glow\DomainEvent;

/**
 * Abstraction for DomainEvent serializers
 */
interface SerializerInterface
{

	public function serialize(DomainEvent $event, $format);

	public function deserialize($eventClass, $data, $format);
}

