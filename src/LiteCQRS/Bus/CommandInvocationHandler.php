<?php

namespace LiteCQRS\Bus;

use LiteCQRS\Command;

class CommandInvocationHandler implements MessageHandlerInterface
{
    private $service;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function handle($command)
    {
        if ( ! is_object($command)) {
            throw new \RuntimeException("No command given to CommandInvocationHandler.");
        }

        $method  = $this->getHandlerMethodName($command);

        if (!method_exists($this->service, $method)) {
            throw new \RuntimeException("Service " . get_class($this->service) . " has no method " . $method . " to handle command.");
        }

        $this->service->$method($command);
    }

    public function getHandlerMethodName($command)
    {
        $parts = explode("\\", get_class($command));

        return str_replace("Command", "", lcfirst(end($parts)));
    }
}

