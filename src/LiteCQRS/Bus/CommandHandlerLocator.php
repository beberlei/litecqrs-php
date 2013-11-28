<?php

namespace LiteCQRS\Bus;

use LiteCQRS\Command;

/**
 * Locator for command handlers based on Command
 *
 * The relationship between commands and handlers
 * should be 1:1. The locator returns a service
 * and not the callback itself. The method name
 * on the service is determined by the command bus.
 */
interface CommandHandlerLocator
{
    public function getCommandHandler(Command $command);
}
