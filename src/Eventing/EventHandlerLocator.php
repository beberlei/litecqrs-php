<?php

namespace LidskaSila\Glow\Eventing;

interface EventHandlerLocator
{

	public function getHandlersFor(EventName $eventName);
}
