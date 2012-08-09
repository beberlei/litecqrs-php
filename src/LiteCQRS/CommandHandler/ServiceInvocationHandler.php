<?php

namespace LiteCQRS\CommandHandler;

use LiteCQRS\Command;

class ServiceInvocationHandler implements CommandHandlerInterface
{
    private $service;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function handle(Command $command)
    {
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

