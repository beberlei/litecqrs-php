<?php

namespace LiteCQRS\Eventing;

interface EventHandlerLocator
{

	public function getHandlersFor(EventName $eventName);
}
