<?php

namespace LiteCQRS\Plugin\SymfonyBundle;

use LiteCQRS\Bus\EventMessageBus;
use LiteCQRS\DomainEvent;
use LiteCQRS\Bus\EventInvocationHandler;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Exception;

class ContainerEventBus implements EventMessageBus
{
    private $container;
    private $services;
    private $proxyFactories;

    public function __construct(ContainerInterface $container, array $proxyFactories = array())
    {
        $this->container = $container;
        $this->proxyFactories = $proxyFactories;
    }

    public function handle(DomainEvent $event)
    {
        $eventName  = $event->getEventName();
        $services   = $this->getHandlers($eventName);

        foreach ($services as $service) {
            try {
                $handler      = new EventInvocationHandler($service);

                foreach ($this->proxyFactories as $proxyFactory) {
                    $handler = $proxyFactory($handler);
                }

                $handler->handle($event);
            } catch(Exception $e) {
            }
        }
    }

    protected function getHandlers($eventName)
    {
        $eventName = strtolower($eventName);

        if (!isset($this->services[$eventName])) {
            return array();
        }

        $services = array();
        foreach ($this->services[$eventName] as $id) {
            $services[] = $this->container->get($id);
        }

        return $services;
    }

    public function registerServices($services)
    {
        $this->services = $services;
    }
}

