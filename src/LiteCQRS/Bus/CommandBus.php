<?php

namespace LiteCQRS\Bus;

use LiteCQRS\Command;

abstract class CommandBus
{
    abstract protected function getService($commandType);

    public function handle(Command $command)
    {
        $type    = get_class($command);
        $service = $this->getService($type);
        $method  = $this->getHandlerMethodName($command);

        $service = $this->wrapHandlerChain($service, $method);

        $service->$method($command);
    }

    public function getHandlerMethodName($command)
    {
        $parts = explode("\\", get_class($command));
        return str_replace("Command", "", lcfirst(end($parts)));
    }

    protected function wrapHandlerChain($service, $method)
    {
        return $service;
    }
}

