<?php
namespace LiteCQRS\Bus;

class DirectCommandBus extends SequentialCommandBus
{
    private $handlers = array();

    public function register($commandType, $service)
    {
        if (!is_object($service)) {
            throw new \RuntimeException("No valid service given for command type '" . $commandType . "'");
        }

        $this->handlers[strtolower($commandType)] = $service;
    }

    protected function getService($commandType)
    {
        if (!isset($this->handlers[strtolower($commandType)])) {
            throw new \RuntimeException("No service registered for command type '" . $commandType . "'");
        }

        return $this->handlers[strtolower($commandType)];
    }
}

