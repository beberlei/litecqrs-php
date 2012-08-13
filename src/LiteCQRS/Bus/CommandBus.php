<?php

namespace LiteCQRS\Bus;

use LiteCQRS\Command;

/**
 * Accept and process commands by passing them along to a matching command handler.
 */
interface CommandBus
{
    public function handle(Command $command);
}

