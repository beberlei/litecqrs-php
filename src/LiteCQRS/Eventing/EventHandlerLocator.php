<?php

namespace LiteCQRS\Eventing;

use LiteCQRS\Eventing\EventName;

interface EventHandlerLocator
{
    public function getHandlersFor(EventName $eventName);
}
