<?php

namespace LiteCQRS\EventHandler;

use LiteCQRS\DomainEvent;

interface EventHandlerInterface
{
    public function handle(DomainEvent $event);
}

