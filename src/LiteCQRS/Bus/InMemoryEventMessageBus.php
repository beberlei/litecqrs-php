<?php
namespace LiteCQRS\Bus;

use LiteCQRS\DomainEvent;
use LiteCQRS\EventHandler\ServiceInvocationHandler;

use Exception;

/**
 * In Memory Event Message Bus
 *
 * You can register Event handlers and every method starting
 * with "on" will be registered as handling an event.
 *
 * By convention the part after the "on" matches the event name.
 * Comparisons are done in lower-case.
 *
 * Exceptions by event handlers are swallowed, no mechanism exists
 * to report event failure back to the developer. This is a rather
 * simple approach and you should see if it works for you.
 */
class InMemoryEventMessageBus implements EventMessageBus
{
    private $handlers = array();
    private $proxyFactory;

    public function __construct($proxyFactory = null)
    {
        $this->proxyFactory = $proxyFactory ?: function($handler) { return $handler; };
    }

    public function handle(DomainEvent $event)
    {
        $eventName  = $event->getEventName();
        $services   = $this->getHandlers($eventName);

        foreach ($services as $service) {
            try {
                $handler      = new ServiceInvocationHandler($service);

                $proxyFactory = $this->proxyFactory;
                $handler      = $proxyFactory($handler);

                $handler->handle($event);
            } catch(Exception $e) {
            }
        }
    }

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

