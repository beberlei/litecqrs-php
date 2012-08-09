<?php
namespace LiteCQRS\Bus;

class DirectCommandBus extends CommandBus
{
    private $handlers = array();
    private $eventBus;

    public function __construct(DirectEventMessageBus $eventBus = null)
    {
        $this->eventBus = $eventBus ?: new DirectEventMessageBus();
    }

    public function register($commandType, $service, $method = null)
    {
        if (!is_object($service)) {
            throw new \RuntimeException("No valid service given for command type '" . $commandType . "'");
        }

        if ($method === null) {
            $parts = explode("\\", $commandType);
            $method = str_replace("Command", "", lcfirst(end($parts)));
        }

        $this->handlers[strtolower($commandType)] = array(
            'service' => $service,
            'method'  => $method
        );
    }

    protected function getService($commandType)
    {
        if (!isset($this->handlers[strtolower($commandType)])) {
            throw new \RuntimeException("No service registered for command type '" . $commandType . "'");
        }

        return $this->handlers[strtolower($commandType)]['service'];
    }

    protected function getCommandMethod($commandType)
    {
        return $this->handlers[strtolower($commandType)]['method'];
    }

    protected function wrapHandlerChain($service, $method)
    {
        return $service;
    }
}

