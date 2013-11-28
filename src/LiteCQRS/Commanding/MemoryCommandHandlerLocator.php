<?php

namespace LiteCQRS\Commanding;

use LiteCQRS\Command;

class MemoryCommandHandlerLocator implements CommandHandlerLocator
{
    private $handlers = array();

    public function getCommandHandler(Command $command)
    {
        $commandType = get_class($command);

        if (!isset($this->handlers[strtolower($commandType)])) {
            throw new \RuntimeException("No service registered for command type '" . $commandType . "'");
        }

        return $this->handlers[strtolower($commandType)];
    }

    public function register($commandType, $service)
    {
        if (!is_object($service)) {
            throw new \RuntimeException("No valid service given for command type '" . $commandType . "'");
        }

        $this->handlers[strtolower($commandType)] = $service;
    }
}
