<?php

namespace LiteCQRS\Bus;

use LiteCQRS\Bus\EventName;

interface EventHandlerLocator
{
    public function getHandlersFor(EventName $eventName);
}
