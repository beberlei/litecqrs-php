<?php

namespace Lidskasila\Glow\Eventing;

interface EventHandlerLocator
{

	public function getHandlersFor(EventName $eventName);
}
