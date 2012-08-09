<?php
namespace LiteCQRS\Bus;

class DirectEventMessageBus extends EventMessageBus
{
    private $handlers = array();

    public function register($handler)
    {
        foreach (get_class_methods($handler) as $methodName) {
            if (strpos($methodName, "on") !== 0) {
                continue;
            }

            $eventName = strtolower(substr($methodName, 2));

            if (!isset($this->handlers[$eventName])) {
                $this->handlers[$eventName] = array();
            }

            $this->handlers[$eventName][] = $handler;
        }
    }

    protected function getHandlers($eventName)
    {
        return $this->handlers[strtolower($eventName)];
    }
}

